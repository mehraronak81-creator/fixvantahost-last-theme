<?php

namespace Pterodactyl\Http\Controllers\Admin\StatusWidget;

use Illuminate\View\View;
use Pterodactyl\Http\Controllers\Controller;

/**
 * Renders the admin management page for the Server Status Widget addon.
 *
 * The actual data and mutations are handled by the dependency-free vanilla JS
 * app (public/js/status-widget-admin.js) loaded by the Blade view. This
 * controller is only responsible for delivering the page shell.
 */
class StatusWidgetController extends Controller
{
    /**
     * Render the Server Status Widget management index page.
     */
    public function index(): View
    {
        return view('admin.status-widget.index');
    }
}

