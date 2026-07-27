<?php

namespace Pterodactyl\Http\Controllers\Admin\Extensions\vantahost;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\Factory as ViewFactory;
use Illuminate\View\View;
use Pterodactyl\BlueprintFramework\Libraries\ExtensionLibrary\Admin\BlueprintAdminLibrary as Blueprint;
use Pterodactyl\Facades\Activity;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Http\Requests\Admin\AdminFormRequest;
use Pterodactyl\Models\{Backup, Node, Server};
use Pterodactyl\Repositories\Wings\DaemonBackupRepository;
use Pterodactyl\Services\Backups\DownloadLinkService;

class vantahostExtensionController extends Controller
{
    public function __construct(private ViewFactory $view, private Blueprint $blueprint, private DaemonBackupRepository $daemon, private DownloadLinkService $downloads) {}

    public function index(): View
    {
        return $this->view->make('admin.extensions.vantahost.index', [
            'backups' => Backup::query()->where('is_successful', true)->whereNotNull('completed_at')->with(['server.user', 'server.node'])->orderByDesc('completed_at')->paginate(25),
            'completedBackups' => Backup::where('is_successful', true)->whereNotNull('completed_at')->count(),
            'lockedBackups' => Backup::where('is_successful', true)->where('is_locked', true)->count(),
            'suspendedServers' => Server::where('status', Server::STATUS_SUSPENDED)->count(),
            'nodes' => Node::withCount('servers')->orderBy('name')->get(),
            'limits' => [
                'command' => $this->blueprint->dbGet('vantahost', 'policy:command_per_minute', 30),
                'power' => $this->blueprint->dbGet('vantahost', 'policy:power_per_minute', 10),
                'file' => $this->blueprint->dbGet('vantahost', 'policy:file_mutations_per_minute', 60),
            ],
        ]);
    }

    public function update(VantaHostAdminRequest $request): RedirectResponse
    {
        if ($request->validated('action') === 'save-policy') {
            $this->blueprint->dbSetMany('vantahost', [
                'policy:command_per_minute' => (int) $request->validated('command_per_minute'),
                'policy:power_per_minute' => (int) $request->validated('power_per_minute'),
                'policy:file_mutations_per_minute' => (int) $request->validated('file_mutations_per_minute'),
            ]);
            $this->blueprint->alert('success', 'Security policy saved. Apply matching limits at your edge or host firewall.');
            return redirect()->route('admin.extensions.vantahost.index');
        }

        $backup = Backup::query()->where('uuid', $request->validated('backup_uuid'))->first();
        $server = $backup?->server;
        if (!$backup?->is_successful || is_null($backup->completed_at) || !$server instanceof Server || !is_null($server->status)) {
            $this->blueprint->alert('danger', 'This backup is unavailable, or its server is not idle.');
            return redirect()->route('admin.extensions.vantahost.index');
        }
        try {
            $backup->loadMissing(['server.node']);
            $url = $backup->disk === Backup::ADAPTER_AWS_S3 ? $this->downloads->handle($backup, $request->user()) : null;
            $server->update(['status' => Server::STATUS_RESTORING_BACKUP]);
            $this->daemon->setServer($server)->restore($backup, $url, false);
            Activity::event('server:backup.restore')->subject($backup)->property(['name' => $backup->name, 'source' => 'blueprint-vantahost', 'truncate' => false])->log();
            $this->blueprint->alert('success', 'Recovery started. The backup will be unpacked over current server files.');
        } catch (\Throwable $exception) {
            $server->update(['status' => null]);
            $this->blueprint->alert('danger', 'Recovery could not be started: ' . $exception->getMessage());
        }
        return redirect()->route('admin.extensions.vantahost.index');
    }
}

class VantaHostAdminRequest extends AdminFormRequest
{
    public function rules(): array
    {
        return ['action' => 'required|in:restore-backup,save-policy', 'backup_uuid' => 'required_if:action,restore-backup|nullable|uuid', 'command_per_minute' => 'required_if:action,save-policy|nullable|integer|min:1|max:10000', 'power_per_minute' => 'required_if:action,save-policy|nullable|integer|min:1|max:10000', 'file_mutations_per_minute' => 'required_if:action,save-policy|nullable|integer|min:1|max:10000'];
    }
}
