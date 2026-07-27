<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Widget — Manage</title>
    <style>
        *{box-sizing:border-box;}
        body{margin:0;min-height:100vh;background:#0b0e14;color:#e4f0ec;
            font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;
            -webkit-font-smoothing:antialiased;}
        .topbar{border-bottom:1px solid rgba(255,255,255,.08);padding:16px 24px;display:flex;
            align-items:center;gap:10px;background:#0d1117;}
        .topbar .dot{width:9px;height:9px;border-radius:50%;background:#10b981;box-shadow:0 0 0 3px rgba(16,185,129,.2);}
        .topbar b{font-size:15px;}
        .topbar a{margin-left:auto;color:#8b97a3;text-decoration:none;font-size:13px;}
        .topbar a:hover{color:#e4f0ec;}
        .container{max-width:980px;margin:0 auto;padding:32px 24px 80px;}
        h1{font-size:24px;margin:0 0 6px;}
        p.lead{color:#8b97a3;margin:0 0 26px;font-size:14px;}
        .notice{border:1px solid rgba(245,158,11,.3);background:rgba(245,158,11,.08);color:#f4d9a6;
            padding:18px 20px;border-radius:14px;font-size:14.5px;}
    </style>
</head>
<body>
    <div class="topbar">
        <span class="dot"></span>
        <b>Server Status Widget</b>
        <a href="/">&larr; Back to dashboard</a>
    </div>

    <div class="container">
        <h1>Manage your status widget</h1>
        <p class="lead">Turn the public status widget on for your servers, pick a theme, restrict where it can be embedded, and grab the copy-paste embed code.</p>

        @if($enabled)
            <div id="status-widget-admin-root"
                 data-base="{{ url('/status-widget') }}"
                 data-csrf="{{ csrf_token() }}"
                 data-ui-theme="dark"></div>
        @else
            <div class="notice">
                Widget management is currently handled by the administrators of this panel. Please contact support if you'd like the status widget enabled for your server.
            </div>
        @endif
    </div>

    @if($enabled)
        <script src="{{ asset('js/status-widget-admin.js') }}"></script>
    @endif
</body>
</html>

