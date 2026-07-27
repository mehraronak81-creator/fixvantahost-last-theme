<!DOCTYPE html>
<html data-theme="dark">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>VantaHost · built by VantaBlack — @yield('title')</title>
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <meta name="_token" content="{{ csrf_token() }}">

        <link rel="apple-touch-icon" sizes="180x180" href="/favicons/apple-touch-icon.png">
        <link rel="icon" type="image/png" href="/favicons/favicon-32x32.png" sizes="32x32">
        <link rel="icon" type="image/png" href="/favicons/favicon-16x16.png" sizes="16x16">
        <link rel="manifest" href="/favicons/manifest.json">
        <link rel="mask-icon" href="/favicons/safari-pinned-tab.svg" color="#4f8cff">
        <link rel="shortcut icon" href="/favicons/favicon.ico">
        <meta name="msapplication-config" content="/favicons/browserconfig.xml">
        <meta name="theme-color" content="#080b12">

        @include('layouts.scripts')

        @section('scripts')
            {!! Theme::css('vendor/select2/select2.min.css?t={cache-version}') !!}
            {!! Theme::css('vendor/bootstrap/bootstrap.min.css?t={cache-version}') !!}
            {!! Theme::css('vendor/adminlte/admin.min.css?t={cache-version}') !!}
            {!! Theme::css('vendor/adminlte/colors/skin-blue.min.css?t={cache-version}') !!}
            {!! Theme::css('vendor/sweetalert/sweetalert.min.css?t={cache-version}') !!}
            {!! Theme::css('vendor/animate/animate.min.css?t={cache-version}') !!}
            {!! Theme::css('css/pterodactyl.css?t={cache-version}') !!}
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">

            <style>
                /* VantaHost Admin Theme Override */
                :root {
                    --vh-accent: #4f8cff;
                    --vh-accent-hover: #76a7ff;
                    --vh-accent-glow: rgba(79, 140, 255, 0.28);
                    --vh-bg: #080b12;
                    --vh-surface: #111827;
                    --vh-surface-2: #192235;
                    --vh-border: #28344a;
                    --vh-text: #f3f6fc;
                    --vh-text-secondary: #c3cce0;
                    --vh-text-muted: #66748e;
                    --vh-danger: #ff4757;
                    --vh-success: #2ed573;
                    --vh-warning: #ffa502;
                }

                body.skin-blue {
                    background-color: var(--vh-bg) !important;
                    background-image:
                        radial-gradient(900px 520px at 7% -8%, rgba(94, 66, 204, .24), transparent 68%),
                        radial-gradient(780px 500px at 98% 12%, rgba(35, 196, 232, .15), transparent 66%),
                        radial-gradient(1px 1px at 18% 22%, rgba(255,255,255,.55), transparent 100%),
                        radial-gradient(1px 1px at 75% 28%, rgba(195,225,255,.55), transparent 100%) !important;
                    background-size: auto, auto, 190px 190px, 260px 260px !important;
                    background-attachment: fixed !important;
                }

                .skin-blue .wrapper,
                .skin-blue .main-sidebar,
                .skin-blue .left-side {
                    background: var(--vh-surface) !important;
                    border-right: 1px solid var(--vh-border);
                }

                .skin-blue .main-header .logo {
                    background: var(--vh-surface) !important;
                    color: var(--vh-text) !important;
                    border-bottom: 1px solid var(--vh-border);
                    font-weight: 600;
                    letter-spacing: 0.02em;
                }

                .skin-blue .main-header .logo:hover {
                    background: var(--vh-surface-2) !important;
                }

                .skin-blue .main-header .navbar {
                    background: var(--vh-surface) !important;
                    border-bottom: 1px solid var(--vh-border);
                }

                .skin-blue .main-header .navbar .sidebar-toggle {
                    color: var(--vh-text-secondary) !important;
                    border-right: 1px solid var(--vh-border);
                }

                .skin-blue .main-header .navbar .sidebar-toggle:hover {
                    background: var(--vh-surface-2) !important;
                    color: var(--vh-accent) !important;
                }

                .skin-blue .main-header .navbar .nav>li>a {
                    color: var(--vh-text-secondary) !important;
                }

                .skin-blue .main-header .navbar .nav>li>a:hover {
                    color: var(--vh-accent) !important;
                    background: var(--vh-surface-2) !important;
                }

                .fixed .main-header {
                    box-shadow: 0 2px 12px rgba(0,0,0,0.4);
                }

                /* Sidebar Styling */
                .skin-blue .sidebar-menu>li.header {
                    color: var(--vh-accent) !important;
                    background: transparent !important;
                    font-size: 11px;
                    font-weight: 700;
                    letter-spacing: 0.08em;
                    text-transform: uppercase;
                    padding: 15px 15px 8px;
                    border-bottom: none;
                }

                .skin-blue .sidebar-menu>li>a {
                    color: var(--vh-text-secondary) !important;
                    transition: all 0.2s ease;
                    border-left: 3px solid transparent;
                    padding: 11px 15px;
                }

                .skin-blue .sidebar-menu>li>a:hover {
                    background: var(--vh-surface-2) !important;
                    color: var(--vh-text) !important;
                    border-left-color: var(--vh-accent);
                }

                .skin-blue .sidebar-menu>li.active>a {
                    background: rgba(108, 92, 231, 0.08) !important;
                    color: var(--vh-accent) !important;
                    border-left-color: var(--vh-accent) !important;
                    font-weight: 600;
                }

                .skin-blue .sidebar-menu>li.active>a>i {
                    color: var(--vh-accent) !important;
                }

                .skin-blue .sidebar-menu>li>a>i {
                    width: 24px;
                    text-align: center;
                    margin-right: 8px;
                }

                /* Content Area */
                .content-wrapper {
                    background: transparent !important;
                    color: var(--vh-text) !important;
                }

                .content-header h1 {
                    color: var(--vh-text) !important;
                }

                /* Boxes / Cards */
                .box {
                    background: linear-gradient(145deg, rgba(25, 34, 53, .88), rgba(14, 20, 34, .88)) !important;
                    border: 1px solid rgba(130, 162, 216, .2) !important;
                    border-top: none !important;
                    border-radius: 12px !important;
                    overflow: hidden;
                    box-shadow: 0 12px 30px rgba(0,0,0,0.25) !important;
                    transition: transform .22s ease, border-color .22s ease, box-shadow .22s ease;
                    backdrop-filter: blur(16px);
                }

                .box:hover {
                    border-color: var(--vh-accent) !important;
                    box-shadow: 0 0 28px var(--vh-accent-glow), 0 18px 42px rgba(0,0,0,0.3) !important;
                    transform: translateY(-2px);
                }

                .box-header {
                    background: var(--vh-surface) !important;
                    color: var(--vh-text) !important;
                    border-bottom: 1px solid var(--vh-border) !important;
                    padding: 12px 15px;
                }

                .box-header .box-title {
                    font-weight: 600;
                    letter-spacing: 0.01em;
                }

                .box-body {
                    color: var(--vh-text-secondary) !important;
                }

                .box-footer {
                    background: var(--vh-surface) !important;
                    border-top: 1px solid var(--vh-border) !important;
                }

                .box.box-success {
                    border-top: 3px solid var(--vh-success) !important;
                }

                .box.box-danger {
                    border-top: 3px solid var(--vh-danger) !important;
                }

                .box.box-warning {
                    border-top: 3px solid var(--vh-warning) !important;
                }

                .box.box-info {
                    border-top: 3px solid var(--vh-accent) !important;
                }

                .box.box-primary {
                    border-top: 3px solid var(--vh-accent) !important;
                }

                /* Footer */
                .main-footer {
                    background: var(--vh-surface) !important;
                    color: var(--vh-text-muted) !important;
                    border-top: 1px solid var(--vh-border) !important;
                }

                /* Tables */
                .table>thead>tr>th,
                .table>tbody>tr>th,
                .table>tfoot>tr>th,
                .table>thead>tr>td,
                .table>tbody>tr>td,
                .table>tfoot>tr>td {
                    border-top-color: var(--vh-border) !important;
                    color: var(--vh-text-secondary);
                }

                .table>thead>tr>th {
                    border-bottom: 2px solid var(--vh-border) !important;
                    color: var(--vh-text) !important;
                    font-weight: 600;
                    text-transform: uppercase;
                    font-size: 11px;
                    letter-spacing: 0.05em;
                }

                .table-hover>tbody>tr:hover {
                    background: var(--vh-surface) !important;
                }

                /* Buttons */
                .btn-success {
                    background: var(--vh-success) !important;
                    border-color: var(--vh-success) !important;
                    border-radius: 8px;
                    font-weight: 600;
                    transition: all 0.2s ease;
                }

                .btn-success:hover {
                    filter: brightness(1.1);
                    box-shadow: 0 4px 12px rgba(46, 213, 115, 0.3);
                }

                .btn-primary {
                    background: linear-gradient(135deg,#3575e8 0%,#4f8cff 48%,#23c4e8 100%) !important;
                    border-color: rgba(148, 197, 255, .55) !important;
                    border-radius: 10px;
                    font-weight: 600;
                    letter-spacing: .025em;
                    transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
                }

                .btn-primary:hover {
                    filter: brightness(1.1);
                    box-shadow: 0 10px 24px var(--vh-accent-glow);
                    transform: translateY(-1px);
                }

                .btn-danger {
                    background: var(--vh-danger) !important;
                    border-color: var(--vh-danger) !important;
                    border-radius: 8px;
                    font-weight: 600;
                }

                .btn-warning {
                    background: var(--vh-warning) !important;
                    border-color: var(--vh-warning) !important;
                    border-radius: 8px;
                    font-weight: 600;
                    color: #fff !important;
                }

                .btn-default {
                    background: var(--vh-surface) !important;
                    color: var(--vh-text-secondary) !important;
                    border-color: var(--vh-border) !important;
                    border-radius: 8px;
                }

                .btn-default:hover {
                    background: var(--vh-surface-2) !important;
                    border-color: var(--vh-accent) !important;
                    color: var(--vh-text) !important;
                }

                /* Forms */
                input.form-control,
                textarea.form-control,
                .form-control {
                    background: var(--vh-surface) !important;
                    border: 1px solid var(--vh-border) !important;
                    border-radius: 8px !important;
                    color: var(--vh-text) !important;
                    transition: border-color 0.2s ease, box-shadow 0.2s ease;
                    padding: 10px 12px;
                }

                input.form-control:focus,
                textarea.form-control:focus,
                .form-control:focus {
                    border-color: var(--vh-accent) !important;
                    box-shadow: 0 0 0 3px var(--vh-accent-glow) !important;
                }

                .input-group .input-group-addon {
                    background: var(--vh-surface) !important;
                    border-color: var(--vh-border) !important;
                    color: var(--vh-text-secondary) !important;
                    border-radius: 8px 0 0 8px;
                }

                /* Select2 */
                .select2-container--default .select2-selection--single,
                .select2-container--default .select2-selection--multiple {
                    background: var(--vh-surface) !important;
                    border-color: var(--vh-border) !important;
                    border-radius: 8px !important;
                }

                .select2-container--default .select2-selection--single .select2-selection__rendered {
                    color: var(--vh-text) !important;
                }

                .select2-dropdown {
                    background: var(--vh-surface-2) !important;
                    border-color: var(--vh-border) !important;
                    border-radius: 8px !important;
                }

                .select2-container--default .select2-results__option--highlighted[aria-selected] {
                    background: var(--vh-accent) !important;
                }

                /* Alerts */
                .alert {
                    border-radius: 10px !important;
                    border: none !important;
                    font-weight: 500;
                }

                .alert-danger {
                    background: rgba(255, 71, 87, 0.15) !important;
                    color: #ff6b81 !important;
                    border-left: 4px solid var(--vh-danger) !important;
                }

                .alert-success {
                    background: rgba(46, 213, 115, 0.15) !important;
                    color: #7bed9f !important;
                    border-left: 4px solid var(--vh-success) !important;
                }

                .alert-warning {
                    background: rgba(255, 165, 2, 0.15) !important;
                    color: #ffc048 !important;
                    border-left: 4px solid var(--vh-warning) !important;
                }

                .alert-info {
                    background: rgba(108, 92, 231, 0.15) !important;
                    color: #a29bfe !important;
                    border-left: 4px solid var(--vh-accent) !important;
                }

                /* Modals */
                .modal-content {
                    border-radius: 16px !important;
                    overflow: hidden;
                    border: 1px solid var(--vh-border);
                }

                .modal-header {
                    background: var(--vh-surface) !important;
                    border-bottom-color: var(--vh-border) !important;
                }

                .modal-body {
                    background: var(--vh-surface-2) !important;
                    color: var(--vh-text-secondary) !important;
                }

                .modal-footer {
                    background: var(--vh-surface) !important;
                    border-top-color: var(--vh-border) !important;
                }

                /* Breadcrumbs */
                .content-header>.breadcrumb>li>a,
                .breadcrumb>.active {
                    color: var(--vh-text-secondary) !important;
                }

                /* Tabs */
                .nav-tabs-custom {
                    background: var(--vh-surface-2) !important;
                    border-radius: 12px !important;
                    overflow: hidden;
                    border: 1px solid var(--vh-border);
                }

                .nav-tabs-custom>.nav-tabs>li.active {
                    border-top-color: var(--vh-accent) !important;
                }

                .nav-tabs-custom>.nav-tabs>li.active>a,
                .nav-tabs-custom>.nav-tabs>li.active:hover>a {
                    background: var(--vh-surface) !important;
                    color: var(--vh-text) !important;
                }

                .nav-tabs-custom>.nav-tabs>li>a {
                    color: var(--vh-text-muted) !important;
                }

                .nav-tabs-custom>.nav-tabs>li>a:hover {
                    color: var(--vh-text) !important;
                }

                /* Code */
                code {
                    background: var(--vh-surface) !important;
                    color: var(--vh-accent) !important;
                    border: 1px solid var(--vh-border) !important;
                    border-radius: 6px;
                    padding: 2px 6px;
                }

                pre {
                    background: var(--vh-surface) !important;
                    color: var(--vh-text-secondary) !important;
                    border-color: var(--vh-border) !important;
                    border-radius: 10px;
                }

                /* Links */
                a { color: var(--vh-accent) !important; }
                a:hover { color: var(--vh-accent-hover) !important; }

                /* Callouts */
                .callout code {
                    background: var(--vh-surface) !important;
                    color: var(--vh-accent) !important;
                }

                /* Well */
                .well {
                    background: var(--vh-surface) !important;
                    border-color: var(--vh-border) !important;
                    border-radius: 10px;
                }

                /* Small boxes */
                .small-box {
                    border-radius: 12px !important;
                    overflow: hidden;
                }

                .small-box h3, .small-box p {
                    color: #fff !important;
                }

                /* Responsive breadcrumbs */
                @media (max-width: 991px) {
                    .content-header>.breadcrumb {
                        background: var(--vh-surface) !important;
                        border-radius: 8px;
                    }
                }

                /* User Image in Header */
                .user-image {
                    border-radius: 50%;
                    border: 2px solid var(--vh-accent);
                }

                /* Pagination */
                .pagination>li>a,
                .pagination>li>span {
                    background: var(--vh-surface) !important;
                    border-color: var(--vh-border) !important;
                    color: var(--vh-text-secondary) !important;
                }

                .pagination>.active>a,
                .pagination>.active>span {
                    background: var(--vh-accent) !important;
                    border-color: var(--vh-accent) !important;
                    color: #fff !important;
                }

                /* Admin Theme Toggle */
                .admin-theme-toggle {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    width: 32px;
                    height: 32px;
                    border-radius: 8px;
                    border: 1px solid var(--vh-border);
                    background: var(--vh-surface);
                    color: var(--vh-text-secondary);
                    cursor: pointer;
                    transition: all 0.2s ease;
                    margin: 0 4px;
                }

                .admin-theme-toggle:hover {
                    background: var(--vh-accent);
                    color: #fff;
                    border-color: var(--vh-accent);
                    box-shadow: 0 0 16px var(--vh-accent-glow);
                }

                /* Light theme overrides for admin */
                [data-theme="light"] body.skin-blue {
                    background: #f0f2f5 !important;
                }

                [data-theme="light"] .content-wrapper {
                    background: #f0f2f5 !important;
                }

                [data-theme="light"] .skin-blue .wrapper,
                [data-theme="light"] .skin-blue .main-sidebar,
                [data-theme="light"] .skin-blue .left-side {
                    background: #fff !important;
                    border-right: 1px solid #e5e7eb;
                }

                [data-theme="light"] .skin-blue .main-header .logo,
                [data-theme="light"] .skin-blue .main-header .navbar {
                    background: #fff !important;
                    border-bottom-color: #e5e7eb;
                }

                [data-theme="light"] .skin-blue .main-header .logo {
                    color: #111827 !important;
                }

                [data-theme="light"] .box {
                    background: #fff !important;
                    border-color: #e5e7eb !important;
                }

                [data-theme="light"] .box-header {
                    background: #f9fafb !important;
                    border-bottom-color: #e5e7eb !important;
                    color: #111827 !important;
                }

                [data-theme="light"] .box-body {
                    color: #4b5563 !important;
                }

                [data-theme="light"] .main-footer {
                    background: #fff !important;
                    border-top-color: #e5e7eb !important;
                    color: #9ca3af !important;
                }

                [data-theme="light"] .content-header h1 {
                    color: #111827 !important;
                }

                [data-theme="light"] input.form-control,
                [data-theme="light"] textarea.form-control,
                [data-theme="light"] .form-control {
                    background: #fff !important;
                    border-color: #e5e7eb !important;
                    color: #111827 !important;
                }

                [data-theme="light"] .skin-blue .sidebar-menu>li>a {
                    color: #4b5563 !important;
                }

                [data-theme="light"] .skin-blue .sidebar-menu>li>a:hover {
                    background: #f3f4f6 !important;
                    color: #111827 !important;
                }

                [data-theme="light"] .skin-blue .sidebar-menu>li.active>a {
                    background: rgba(79, 140, 255, 0.08) !important;
                    color: #3575e8 !important;
                }

                [data-theme="light"] .skin-blue .sidebar-menu>li.header {
                    color: #3575e8 !important;
                }

                [data-theme="light"] .table>thead>tr>th,
                [data-theme="light"] .table>tbody>tr>td {
                    border-color: #e5e7eb !important;
                    color: #4b5563;
                }

                [data-theme="light"] .table>thead>tr>th {
                    color: #111827 !important;
                }

                [data-theme="light"] .table-hover>tbody>tr:hover {
                    background: #f9fafb !important;
                }

                [data-theme="light"] code {
                    background: #f3f4f6 !important;
                    border-color: #e5e7eb !important;
                }

                [data-theme="light"] .alert-danger {
                    background: rgba(239, 68, 68, 0.08) !important;
                    color: #dc2626 !important;
                }

                [data-theme="light"] .alert-success {
                    background: rgba(34, 197, 94, 0.08) !important;
                    color: #16a34a !important;
                }
            </style>
        @show
    </head>
    <body class="hold-transition skin-blue fixed sidebar-mini">
        <div class="wrapper">
            <header class="main-header">
                <a href="{{ route('index') }}" class="logo">
                    <span style="display:flex;align-items:center;justify-content:center;gap:8px;">
                        <span style="width:26px;height:26px;background:linear-gradient(135deg,#4f8cff,#23c4e8);border-radius:7px;display:inline-flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px;box-shadow:0 0 12px rgba(79,140,255,0.4);">V</span>
                        VantaHost <small style="font-size:10px;opacity:.65;">by VantaBlack</small>
                    </span>
                </a>
                <nav class="navbar navbar-static-top">
                    <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>
                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">
                            <li>
                                <a href="#" class="admin-theme-toggle" id="adminThemeToggle" title="Toggle Theme" onclick="toggleAdminTheme(); return false;">
                                    <i class="fa fa-moon-o" id="themeIcon"></i>
                                </a>
                            </li>
                            <li class="user-menu">
                                <a href="{{ route('account') }}">
                                    <img src="https://www.gravatar.com/avatar/{{ md5(strtolower(Auth::user()->email)) }}?s=160" class="user-image" alt="User Image">
                                    <span class="hidden-xs">{{ Auth::user()->name_first }} {{ Auth::user()->name_last }}</span>
                                </a>
                            </li>
                            <li>
                                <li><a href="{{ route('index') }}" data-toggle="tooltip" data-placement="bottom" title="Exit Admin Control"><i class="fa fa-server"></i></a></li>
                            </li>
                            <li>
                                <li><a href="{{ route('auth.logout') }}" id="logoutButton" data-toggle="tooltip" data-placement="bottom" title="Logout"><i class="fa fa-sign-out"></i></a></li>
                            </li>
                        </ul>
                    </div>
                </nav>
            </header>
            <aside class="main-sidebar">
                <section class="sidebar">
                    <ul class="sidebar-menu">
                        <li class="header">BASIC ADMINISTRATION</li>
                        <li class="{{ Route::currentRouteName() !== 'admin.index' ?: 'active' }}">
                            <a href="{{ route('admin.index') }}">
                                <i class="fa fa-home"></i> <span>Overview</span>
                            </a>
                        </li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.settings') ?: 'active' }}">
                            <a href="{{ route('admin.settings')}}">
                                <i class="fa fa-wrench"></i> <span>Settings</span>
                            </a>
                        </li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.api') ?: 'active' }}">
                            <a href="{{ route('admin.api.index')}}">
                                <i class="fa fa-gamepad"></i> <span>Application API</span>
                            </a>
                        </li>
                        <li class="header">MANAGEMENT</li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.databases') ?: 'active' }}">
                            <a href="{{ route('admin.databases') }}">
                                <i class="fa fa-database"></i> <span>Databases</span>
                            </a>
                        </li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.locations') ?: 'active' }}">
                            <a href="{{ route('admin.locations') }}">
                                <i class="fa fa-globe"></i> <span>Locations</span>
                            </a>
                        </li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.announcements') ?: 'active' }}">
                            <a href="{{ route('admin.announcements') }}">
                                <i class="fa fa-bullhorn"></i> <span>Announcements</span>
                            </a>
                        </li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.nodes') ?: 'active' }}">
                            <a href="{{ route('admin.nodes') }}">
                                <i class="fa fa-sitemap"></i> <span>Nodes</span>
                            </a>
                        </li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.servers') ?: 'active' }}">
                            <a href="{{ route('admin.servers') }}">
                                <i class="fa fa-server"></i> <span>Servers</span>
                            </a>
                        </li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.users') ?: 'active' }}">
                            <a href="{{ route('admin.users') }}">
                                <i class="fa fa-users"></i> <span>Users</span>
                            </a>
                        </li>
                        <li class="header">SERVICE MANAGEMENT</li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.mounts') ?: 'active' }}">
                            <a href="{{ route('admin.mounts') }}">
                                <i class="fa fa-magic"></i> <span>Mounts</span>
                            </a>
                        </li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.nests') ?: 'active' }}">
                            <a href="{{ route('admin.nests') }}">
                                <i class="fa fa-th-large"></i> <span>Nests</span>
                            </a>
                        </li>
                        <li class="header">ADMIN TOOLS</li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.bulk-actions') ?: 'active' }}">
                            <a href="{{ route('admin.bulk-actions') }}">
                                <i class="fa fa-tasks"></i> <span>Bulk Actions</span>
                            </a>
                        </li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.trashbin') ?: 'active' }}">
                            <a href="{{ route('admin.trashbin') }}">
                                <i class="fa fa-trash"></i> <span>Trash Bin</span>
                                @php
                                    $trashCount = \Pterodactyl\Models\Server::where('status', \Pterodactyl\Models\Server::STATUS_SUSPENDED)->count();
                                @endphp
                                @if($trashCount > 0)
                                    <span class="pull-right-container">
                                        <small class="label pull-right" style="background:var(--vh-danger);">{{ $trashCount }}</small>
                                    </span>
                                @endif
                            </a>
                        </li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.recovery') ?: 'active' }}">
                            <a href="{{ route('admin.recovery.index') }}">
                                <i class="fa fa-life-ring"></i> <span>Recovery Centre</span>
                            </a>
                        </li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.backup-compliance') ?: 'active' }}">
                            <a href="{{ route('admin.backup-compliance.index') }}">
                                <i class="fa fa-check-circle"></i> <span>Backup Compliance</span>
                            </a>
                        </li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.security') ?: 'active' }}">
                            <a href="{{ route('admin.security.index') }}">
                                <i class="fa fa-shield"></i> <span>Security Centre</span>
                            </a>
                        </li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.status-widget') ?: 'active' }}">
                            <a href="{{ route('admin.status-widget.index') }}">
                                <i class="fa fa-signal"></i> <span>Server Status Widget</span>
                            </a>
                        </li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.maintenance') ?: 'active' }}">
                            <a href="{{ route('admin.maintenance') }}">
                                <i class="fa fa-wrench"></i> <span>Maintenance</span>
                                @php
                                    $maintCount = \Pterodactyl\Models\Node::where('maintenance_mode', true)->count();
                                @endphp
                                @if($maintCount > 0)
                                    <span class="pull-right-container">
                                        <small class="label pull-right" style="background:var(--vh-warning);">{{ $maintCount }}</small>
                                    </span>
                                @endif
                            </a>
                        </li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.activity') ?: 'active' }}">
                            <a href="{{ route('admin.activity') }}">
                                <i class="fa fa-history"></i> <span>Activity Log</span>
                            </a>
                        </li>
                        <li class="header">SYSTEM HEALTH</li>
                        <li class="{{ ! starts_with(Route::currentRouteName(), 'admin.health') ?: 'active' }}">
                            <a href="{{ route('admin.health') }}">
                                <i class="fa fa-heartbeat"></i> <span>Health Monitor</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.index') }}#quick-actions">
                                <i class="fa fa-bolt"></i> <span>Quick Actions</span>
                            </a>
                        </li>
                    </ul>
                </section>
            </aside>
            <div class="content-wrapper">
                <section class="content-header">
                    @yield('content-header')
                </section>
                <section class="content">
                    <div class="row">
                        <div class="col-xs-12">
                            @if (count($errors) > 0)
                                <div class="alert alert-danger">
                                    There was an error validating the data provided.<br><br>
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            @foreach (Alert::getMessages() as $type => $messages)
                                @foreach ($messages as $message)
                                    <div class="alert alert-{{ $type }} alert-dismissable" role="alert">
                                        {{ $message }}
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                    @yield('content')
                </section>
            </div>
            <footer class="main-footer">
                <div class="pull-right small" style="margin-right:10px;margin-top:-7px;color:var(--vh-text-muted);">
                    <strong><i class="fa fa-fw {{ $appIsGit ? 'fa-git-square' : 'fa-code-fork' }}"></i></strong> {{ $appVersion }}<br />
                    <strong><i class="fa fa-fw fa-clock-o"></i></strong> {{ round(microtime(true) - LARAVEL_START, 3) }}s
                </div>
                Copyright &copy; 2024 - {{ date('Y') }} <a href="#">VantaHost</a>, built by VantaBlack. Powered by Pterodactyl.
            </footer>
        </div>
        @section('footer-scripts')
            <script src="/js/keyboard.polyfill.js" type="application/javascript"></script>
            <script>keyboardeventKeyPolyfill.polyfill();</script>

            {!! Theme::js('vendor/jquery/jquery.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/sweetalert/sweetalert.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/bootstrap/bootstrap.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/slimscroll/jquery.slimscroll.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/adminlte/app.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/bootstrap-notify/bootstrap-notify.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/select2/select2.full.min.js?t={cache-version}') !!}
            {!! Theme::js('js/admin/functions.js?t={cache-version}') !!}
            <script src="/js/autocomplete.js" type="application/javascript"></script>

            <script>
                // VantaHost Admin Theme Toggle
                function toggleAdminTheme() {
                    var html = document.documentElement;
                    var current = html.getAttribute('data-theme') || 'dark';
                    var next = current === 'dark' ? 'light' : 'dark';
                    html.setAttribute('data-theme', next);
                    localStorage.setItem('vantahost-admin-theme', next);
                    var icon = document.getElementById('themeIcon');
                    if (icon) {
                        icon.className = next === 'dark' ? 'fa fa-moon-o' : 'fa fa-sun-o';
                    }
                }

                // Apply saved theme on load
                (function() {
                    var saved = localStorage.getItem('vantahost-admin-theme') || 'dark';
                    document.documentElement.setAttribute('data-theme', saved);
                    var icon = document.getElementById('themeIcon');
                    if (icon) {
                        icon.className = saved === 'dark' ? 'fa fa-moon-o' : 'fa fa-sun-o';
                    }
                })();
            </script>

            @if(Auth::user()->root_admin)
                <script>
                    $('#logoutButton').on('click', function (event) {
                        event.preventDefault();

                        var that = this;
                        swal({
                            title: 'Do you want to log out?',
                            type: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d9534f',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Log out'
                        }, function () {
                             $.ajax({
                                type: 'POST',
                                url: '{{ route('auth.logout') }}',
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },complete: function () {
                                    window.location.href = '{{route('auth.login')}}';
                                }
                        });
                    });
                });
                </script>
            @endif

            <script>
                $(function () {
                    $('[data-toggle="tooltip"]').tooltip();
                })
            </script>
        @show
    </body>
</html>
