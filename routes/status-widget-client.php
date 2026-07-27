<?php

use Illuminate\Support\Facades\Route;
use Pterodactyl\Http\Controllers\Client\StatusWidget\ClientStatusWidgetController;

/*
|--------------------------------------------------------------------------
| Status Widget — Client Routes
|--------------------------------------------------------------------------
|
| Registered from RouteServiceProvider with the panel's `web` + `auth`
| middleware (and BEFORE the SPA catch-all in routes/base.php so these paths
| are not swallowed by the dashboard router). They let a logged-in server
| owner manage the widget for their own servers at /status-widget.
|
| Authorization is enforced in the controller: the global client-management
| toggle must be on, and the user must own the server being changed.
|
*/

Route::prefix('status-widget')->group(function () {
    Route::get('/', [ClientStatusWidgetController::class, 'index'])
        ->name('status-widget.client.index');

    Route::get('/data', [ClientStatusWidgetController::class, 'list'])
        ->name('status-widget.client.data');

    // Single-server endpoints (short UUID) used by the React Settings-tab section.
    Route::get('/server/{server}', [ClientStatusWidgetController::class, 'show'])
        ->name('status-widget.client.show');
    Route::patch('/server/{server}', [ClientStatusWidgetController::class, 'updateOne'])
        ->name('status-widget.client.update-one');

    Route::patch('/{server}', [ClientStatusWidgetController::class, 'update'])
        ->name('status-widget.client.update');
});

