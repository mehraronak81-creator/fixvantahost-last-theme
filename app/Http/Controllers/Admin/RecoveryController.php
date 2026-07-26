<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Facades\Activity;
use Pterodactyl\Models\Backup;
use Pterodactyl\Models\Server;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Services\Backups\DownloadLinkService;
use Pterodactyl\Repositories\Wings\DaemonBackupRepository;

/**
 * Gives root administrators a controlled recovery path for files that were
 * accidentally removed. A backup is unpacked over the current directory, so
 * current files are retained unless Wings itself reports an error.
 */
class RecoveryController extends Controller
{
    public function __construct(
        private AlertsMessageBag $alert,
        private DaemonBackupRepository $daemonRepository,
        private DownloadLinkService $downloadLinkService,
    ) {
    }

    public function index(): View
    {
        $backups = Backup::query()
            ->where('is_successful', true)
            ->whereNotNull('completed_at')
            ->with(['server.user', 'server.node'])
            ->orderByDesc('completed_at')
            ->paginate(25);

        return view('admin.recovery.index', ['backups' => $backups]);
    }

    public function restore(Request $request, Backup $backup): RedirectResponse
    {
        $backup->loadMissing(['server.node']);
        $server = $backup->server;

        if (!$backup->is_successful || is_null($backup->completed_at) || !$server instanceof Server) {
            $this->alert->danger('This backup is not available for recovery.')->flash();

            return redirect()->route('admin.recovery.index');
        }

        if (!is_null($server->status)) {
            $this->alert->warning('Recovery is available only while the server is fully installed and idle.')->flash();

            return redirect()->route('admin.recovery.index');
        }

        $previousStatus = $server->status;

        try {
            $url = $backup->disk === Backup::ADAPTER_AWS_S3
                ? $this->downloadLinkService->handle($backup, $request->user())
                : null;

            $server->update(['status' => Server::STATUS_RESTORING_BACKUP]);
            $this->daemonRepository->setServer($server)->restore($backup, $url, false);

            Activity::event('server:backup.restore')
                ->subject($backup)
                ->property(['name' => $backup->name, 'source' => 'admin-recovery-centre', 'truncate' => false])
                ->log();

            $this->alert->success('Recovery has started. The backup will be unpacked over the current server files.')->flash();
        } catch (\Throwable $exception) {
            $server->update(['status' => $previousStatus]);
            $this->alert->danger('Recovery could not be started: ' . $exception->getMessage())->flash();
        }

        return redirect()->route('admin.recovery.index');
    }
}
