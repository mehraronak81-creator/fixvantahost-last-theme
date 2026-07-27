<?php

namespace Pterodactyl\Http\Controllers\Client\StatusWidget;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Pterodactyl\Models\Server;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Models\ServerStatusWidgetConfig;
use Pterodactyl\Models\ServerStatusWidgetSetting;

/**
 * Lets a server owner manage the status widget for their OWN servers from the
 * client area. Every action is gated by two checks:
 *
 *   1. The global "client management" toggle must be enabled by an admin.
 *   2. The authenticated user must own the server being modified.
 *
 * These routes run under the panel's web + auth middleware, so the user is
 * always authenticated and requests are CSRF-protected.
 */
class ClientStatusWidgetController extends Controller
{
    /**
     * Render the standalone client management page.
     */
    public function index(Request $request): View
    {
        return view('status-widget.client', [
            'enabled' => ServerStatusWidgetConfig::clientManagementEnabled(),
        ]);
    }

    /**
     * Return the servers this user owns, each with its widget configuration.
     */
    public function list(Request $request): JsonResponse
    {
        $this->ensureEnabled();

        $servers = Server::query()
            ->with(['node'])
            ->where('owner_id', $request->user()->id)
            ->orderBy('name')
            ->get();

        $settings = ServerStatusWidgetSetting::query()
            ->whereIn('server_id', $servers->pluck('id'))
            ->get()
            ->keyBy('server_id');

        $data = $servers->map(function (Server $server) use ($settings) {
            /** @var ServerStatusWidgetSetting|null $setting */
            $setting = $settings->get($server->id);

            return [
                'id' => (int) $server->id,
                'uuidShort' => $server->uuidShort,
                'name' => $server->name,
                'node' => $server->node?->name ?? '',
                'enabled' => $setting !== null ? (bool) $setting->enabled : false,
                'theme' => $setting?->theme ?? 'dark',
                'allowed_domains' => $setting?->allowed_domains,
            ];
        })->values();

        return response()->json(['data' => $data]);
    }

    /**
     * Update the widget configuration for one of the user's own servers.
     */
    public function update(Request $request, int $server): JsonResponse
    {
        $this->ensureEnabled();

        $data = $request->validate([
            'enabled' => ['required', 'boolean'],
            'theme' => ['required', 'string', 'in:dark,light'],
            'allowed_domains' => ['nullable', 'string'],
        ]);

        /** @var Server $model */
        $model = Server::query()->findOrFail($server);

        // Ownership check — a user may only touch servers they own.
        if ((int) $model->owner_id !== (int) $request->user()->id) {
            throw new AccessDeniedHttpException('You do not own this server.');
        }

        $allowedDomains = $data['allowed_domains'] ?? null;
        if ($allowedDomains !== null && trim($allowedDomains) === '') {
            $allowedDomains = null;
        }

        /** @var ServerStatusWidgetSetting $setting */
        $setting = ServerStatusWidgetSetting::query()->updateOrCreate(
            ['server_id' => $model->id],
            [
                'enabled' => (bool) $data['enabled'],
                'theme' => $data['theme'],
                'allowed_domains' => $allowedDomains,
            ]
        );

        return response()->json([
            'id' => (int) $setting->id,
            'server_id' => (int) $setting->server_id,
            'enabled' => (bool) $setting->enabled,
            'theme' => $setting->theme,
            'allowed_domains' => $setting->allowed_domains,
        ]);
    }

    /**
     * Single-server read, keyed by the server's short UUID. Used by the React
     * section embedded in the client server Settings tab.
     */
    public function show(Request $request, string $server): JsonResponse
    {
        $this->ensureEnabled();

        $model = $this->ownedServerByShort($request, $server);
        $setting = ServerStatusWidgetSetting::query()->where('server_id', $model->id)->first();

        return response()->json([
            'uuidShort' => $model->uuidShort,
            'enabled' => $setting !== null ? (bool) $setting->enabled : false,
            'theme' => $setting?->theme ?? 'dark',
            'allowed_domains' => $setting?->allowed_domains,
        ]);
    }

    /**
     * Single-server update, keyed by the server's short UUID.
     */
    public function updateOne(Request $request, string $server): JsonResponse
    {
        $this->ensureEnabled();

        $model = $this->ownedServerByShort($request, $server);

        $data = $request->validate([
            'enabled' => ['required', 'boolean'],
            'theme' => ['required', 'string', 'in:dark,light'],
            'allowed_domains' => ['nullable', 'string'],
        ]);

        $allowedDomains = $data['allowed_domains'] ?? null;
        if ($allowedDomains !== null && trim($allowedDomains) === '') {
            $allowedDomains = null;
        }

        /** @var ServerStatusWidgetSetting $setting */
        $setting = ServerStatusWidgetSetting::query()->updateOrCreate(
            ['server_id' => $model->id],
            [
                'enabled' => (bool) $data['enabled'],
                'theme' => $data['theme'],
                'allowed_domains' => $allowedDomains,
            ]
        );

        return response()->json([
            'uuidShort' => $model->uuidShort,
            'enabled' => (bool) $setting->enabled,
            'theme' => $setting->theme,
            'allowed_domains' => $setting->allowed_domains,
        ]);
    }

    /**
     * Resolve a server by short UUID and ensure the user owns it.
     */
    private function ownedServerByShort(Request $request, string $short): Server
    {
        /** @var Server|null $model */
        $model = Server::query()->where('uuidShort', $short)->first();

        if ($model === null) {
            throw new NotFoundHttpException('Server not found.');
        }

        if ((int) $model->owner_id !== (int) $request->user()->id) {
            throw new AccessDeniedHttpException('You do not own this server.');
        }

        return $model;
    }

    /**
     * Abort when an admin has turned off client-side management.
     */
    private function ensureEnabled(): void
    {
        if (! ServerStatusWidgetConfig::clientManagementEnabled()) {
            throw new AccessDeniedHttpException('Widget management is currently handled by the administrators.');
        }
    }
}

