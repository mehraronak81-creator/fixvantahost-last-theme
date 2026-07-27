<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Models\Server;

/**
 * Shows active servers that have no completed backup inside the selected
 * recovery window. This is read-only: it does not create, alter, or delete
 * backups, servers, or files.
 */
class BackupComplianceController extends Controller
{
    public function index(Request $request): View
    {
        $days = max(1, min(365, (int) $request->input('days', 7)));
        $cutoff = now()->subDays($days);
        $completedBackup = static fn ($query) => $query
            ->where('is_successful', true)
            ->whereNotNull('completed_at');

        $activeServers = Server::query()->whereNull('status');
        $totalServers = (clone $activeServers)->count();
        $protectedServers = (clone $activeServers)
            ->whereHas('backups', fn ($query) => $completedBackup($query)->where('completed_at', '>=', $cutoff))
            ->count();
        $serversWithoutBackup = (clone $activeServers)
            ->whereDoesntHave('backups', $completedBackup)
            ->count();

        $atRiskServers = (clone $activeServers)
            ->with(['node', 'user'])
            ->withMax(['backups as latest_successful_backup_at' => $completedBackup], 'completed_at')
            ->whereDoesntHave('backups', fn ($query) => $completedBackup($query)->where('completed_at', '>=', $cutoff))
            ->orderByRaw('latest_successful_backup_at IS NULL DESC')
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('admin.backup-compliance.index', [
            'days' => $days,
            'cutoff' => $cutoff,
            'totalServers' => $totalServers,
            'protectedServers' => $protectedServers,
            'serversWithoutBackup' => $serversWithoutBackup,
            'staleServers' => max(0, $totalServers - $protectedServers - $serversWithoutBackup),
            'atRiskServers' => $atRiskServers,
        ]);
    }
}
