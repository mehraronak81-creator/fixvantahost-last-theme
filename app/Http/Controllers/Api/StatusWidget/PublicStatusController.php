<?php

namespace Pterodactyl\Http\Controllers\Api\StatusWidget;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Models\ServerStatusWidgetSetting;
use Pterodactyl\Services\StatusWidget\ServerStatusService;

/**
 * Public, unauthenticated endpoint that powers the embeddable status widget.
 *
 * The {server} route parameter is always the public 8-character uuidShort of a
 * server, never the numeric primary key. Responses never distinguish between a
 * missing server, a server without a widget setting, and a disabled widget so
 * that the existence of servers is not leaked to anonymous callers.
 */
class PublicStatusController extends Controller
{
    public function __construct(private ServerStatusService $statusService)
    {
    }

    /**
     * Return the current public status payload for a server.
     *
     * @param string $server The server's public uuidShort.
     */
    public function show(Request $request, string $server): JsonResponse
    {
        $model = $this->statusService->getServerByUuidShort($server);

        if ($model === null) {
            return response()->json(['error' => 'not_found'], 404);
        }

        /** @var ServerStatusWidgetSetting|null $setting */
        $setting = ServerStatusWidgetSetting::query()
            ->where('server_id', $model->id)
            ->first();

        if ($setting === null || ! $setting->enabled) {
            return response()->json(['error' => 'not_found'], 404);
        }

        return response()->json($this->statusService->cachedPayload($model, $setting));
    }
}

