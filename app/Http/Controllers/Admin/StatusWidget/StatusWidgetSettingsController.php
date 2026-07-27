<?php

namespace Pterodactyl\Http\Controllers\Admin\StatusWidget;

use Illuminate\Http\Request;
use Pterodactyl\Models\Server;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Models\ServerStatusWidgetConfig;
use Pterodactyl\Models\ServerStatusWidgetSetting;

/**
 * JSON API backing the admin management UI for the status widget.
 *
 * All routes here live inside the existing admin auth route group (web + auth +
 * admin middleware), so requests are CSRF-protected and require an admin user.
 */
class StatusWidgetSettingsController extends Controller
{
    /**
     * Return every server together with its current widget configuration.
     *
     * The shape is consumed verbatim by the admin table UI, so the keys and
     * value types must remain stable.
     */
    public function list(): JsonResponse
    {
        $servers = Server::query()
            ->with(['node'])
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
     * Create or update the widget configuration for a single server.
     *
     * The {server} route parameter is the numeric server id. The row is upserted
     * keyed by server_id so that toggling a server on for the first time creates
     * the backing setting transparently.
     */
    public function update(Request $request, int $server): JsonResponse
    {
        $data = $request->validate([
            'enabled' => ['required', 'boolean'],
            'theme' => ['required', 'string', 'in:dark,light'],
            'allowed_domains' => ['nullable', 'string'],
        ]);

        // Ensure the server actually exists before persisting a setting for it.
        $model = Server::query()->findOrFail($server);

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
     * Return the global addon configuration (admin-only).
     */
    public function config(): JsonResponse
    {
        return response()->json([
            'client_management_enabled' => ServerStatusWidgetConfig::clientManagementEnabled(),
        ]);
    }

    /**
     * Update the global addon configuration (admin-only). Currently just the
     * toggle that lets server owners manage their own widget from the client area.
     */
    public function updateConfig(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_management_enabled' => ['required', 'boolean'],
        ]);

        ServerStatusWidgetConfig::set(
            ServerStatusWidgetConfig::KEY_CLIENT_MANAGEMENT,
            $data['client_management_enabled'] ? '1' : '0'
        );

        return response()->json([
            'client_management_enabled' => (bool) $data['client_management_enabled'],
        ]);
    }
}

