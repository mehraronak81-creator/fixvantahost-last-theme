<?php

use Illuminate\Support\Facades\Route;
use Pterodactyl\Http\Middleware\StatusWidget\WidgetCors;
use Pterodactyl\Http\Controllers\Api\StatusWidget\PublicStatusController;

/*
|--------------------------------------------------------------------------
| Status Widget — Public (unauthenticated) Routes
|--------------------------------------------------------------------------
|
| These routes power the embeddable, cross-origin status widget. They are
| registered by the addon's RouteServiceProvider rather than being required
| into any core route file, which keeps the "api/status-widget" prefix isolated
| and avoids collisions with Pterodactyl's existing route groups.
|
| The {server} parameter is always a server's public 8-character uuidShort.
|
*/

Route::prefix('api/status-widget')
    ->middleware([WidgetCors::class, 'throttle:120,1'])
    ->group(function () {
        Route::get('/{server}', [PublicStatusController::class, 'show'])
            ->name('status-widget.public.show');

        // CORS preflight. The WidgetCors middleware short-circuits OPTIONS with a
        // 204 carrying the appropriate headers, so this handler is a safety net
        // and is never expected to execute.
        Route::options('/{server}', function () {
            return response('', 204);
        })->name('status-widget.public.preflight');
    });

