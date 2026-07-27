<?php

use Illuminate\Support\Facades\Route;
use Pterodactyl\Http\Controllers\Admin\StatusWidget\StatusWidgetController;
use Pterodactyl\Http\Controllers\Admin\StatusWidget\StatusWidgetSettingsController;

/*
|--------------------------------------------------------------------------
| Status Widget — Admin Routes
|--------------------------------------------------------------------------
|
| This file is loaded via a `require` from the panel's own routes/admin.php,
| so these routes automatically inherit Pterodactyl's existing admin route
| group: the `/admin` prefix AND whatever middleware stack protects the rest
| of the admin area (session auth + admin gate + CSRF). We therefore declare
| ONLY a relative `status-widget` prefix and NO middleware of our own — this
| guarantees identical authentication to every other admin page and never
| depends on a middleware alias that may not exist on a given panel.
|
| Final paths: /admin/status-widget, /admin/status-widget/data,
|              /admin/status-widget/{server} (PATCH).
|
*/

Route::prefix('status-widget')->group(function () {
    Route::get('/', [StatusWidgetController::class, 'index'])
        ->name('admin.status-widget.index');

    Route::get('/data', [StatusWidgetSettingsController::class, 'list'])
        ->name('admin.status-widget.data');

    // Global addon configuration (e.g. the client-management toggle).
    Route::get('/config', [StatusWidgetSettingsController::class, 'config'])
        ->name('admin.status-widget.config');
    Route::patch('/config', [StatusWidgetSettingsController::class, 'updateConfig'])
        ->name('admin.status-widget.config.update');

    Route::patch('/{server}', [StatusWidgetSettingsController::class, 'update'])
        ->name('admin.status-widget.update');
});

