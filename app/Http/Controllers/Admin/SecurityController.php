<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Pterodactyl\Models\Backup;
use Pterodactyl\Models\Node;
use Pterodactyl\Models\Server;
use Pterodactyl\Http\Controllers\Controller;

/**
 * Provides an operational view of Panel-side safeguards and the node actions
 * that an administrator should take when investigating abuse.
 */
class SecurityController extends Controller
{
    public function index(): View
    {
        $nodes = Node::withCount('servers')->orderBy('name')->get();

        return view('admin.security.index', [
            'clientApiLimit' => config('http.rate_limit.client'),
            'applicationApiLimit' => config('http.rate_limit.application'),
            'commandLimit' => config('vantahost.abuse.command_per_minute'),
            'powerLimit' => config('vantahost.abuse.power_per_minute'),
            'fileMutationLimit' => config('vantahost.abuse.file_mutations_per_minute'),
            'completedBackups' => Backup::where('is_successful', true)->whereNotNull('completed_at')->count(),
            'lockedBackups' => Backup::where('is_successful', true)->where('is_locked', true)->count(),
            'suspendedServers' => Server::where('status', Server::STATUS_SUSPENDED)->count(),
            'nodes' => $nodes,
        ]);
    }
}
