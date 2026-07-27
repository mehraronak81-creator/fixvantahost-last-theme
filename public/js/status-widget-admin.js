/*!
 * Server Status Widget - management UI (vanilla JS, zero dependencies)
 *
 * Self-styled and self-contained: it injects its own scoped CSS (prefix
 * "sswa-") so it renders correctly anywhere - the AdminLTE admin page AND the
 * standalone dark client page - with no build step and no Bootstrap/AdminLTE
 * dependency. Mounts into #status-widget-admin-root and reads its config from
 * that element's data attributes:
 *
 *   data-base       admin or client base URL (e.g. /admin/status-widget)
 *   data-csrf       Laravel CSRF token (for PATCH requests)
 *   data-ui-theme   "light" (default, admin) or "dark" (client page)
 *   data-config     "1" to show the admin-only global settings panel
 */
(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn, false);
        } else { fn(); }
    }

    var STYLE_ID = 'sswa-styles';

    function injectStyles() {
        if (document.getElementById(STYLE_ID)) { return; }
        var css = [
            '.sswa{--accent:#10b981;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;font-size:14px;}',
            '.sswa.sswa-light{--text:#1f2733;--muted:#6b7280;--border:#e5e7eb;--card:#ffffff;--input-bg:#ffffff;--input-bd:#d1d5db;--hover:#f9fafb;--thead:#f3f4f6;--track:#d1d5db;}',
            '.sswa.sswa-dark{--text:#e4f0ec;--muted:#8b97a3;--border:rgba(255,255,255,.10);--card:#0d1117;--input-bg:#0b0e14;--input-bd:rgba(255,255,255,.14);--hover:rgba(255,255,255,.03);--thead:rgba(255,255,255,.04);--track:rgba(255,255,255,.16);}',
            '.sswa *{box-sizing:border-box;}',
            '.sswa-card{border:1px solid var(--border);background:var(--card);border-radius:12px;padding:16px 18px;margin-bottom:18px;color:var(--text);}',
            '.sswa-card h3{margin:0 0 4px;font-size:15px;}',
            '.sswa-card p{margin:0;color:var(--muted);font-size:13px;}',
            '.sswa-set-row{display:flex;align-items:center;gap:14px;}',
            '.sswa-set-row .sswa-set-txt{flex:1 1 auto;min-width:0;}',
            '.sswa-msg{color:var(--muted);padding:8px 2px;font-size:13.5px;}',
            '.sswa-msg.err{color:#dc2626;}',
            '.sswa-tablewrap{border:1px solid var(--border);border-radius:12px;overflow:auto;background:var(--card);}',
            '.sswa-table{border-collapse:collapse;width:100%;min-width:720px;color:var(--text);}',
            '.sswa-table th{font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);text-align:left;padding:11px 14px;background:var(--thead);border-bottom:1px solid var(--border);white-space:nowrap;}',
            '.sswa-table td{padding:11px 14px;border-bottom:1px solid var(--border);vertical-align:middle;}',
            '.sswa-table tr:last-child td{border-bottom:none;}',
            '.sswa-table tbody tr:hover{background:var(--hover);}',
            '.sswa-name{font-weight:600;}',
            '.sswa-sub{color:var(--muted);font-size:12px;}',
            '.sswa-mono{font-family:"SFMono-Regular",Consolas,Menlo,monospace;font-size:12.5px;color:var(--accent);}',
            '.sswa-switch{position:relative;display:inline-block;width:44px;height:24px;vertical-align:middle;}',
            '.sswa-switch input{opacity:0;width:0;height:0;position:absolute;}',
            '.sswa-slider{position:absolute;cursor:pointer;inset:0;background:var(--track);border-radius:24px;transition:.2s;}',
            '.sswa-slider:before{content:"";position:absolute;height:18px;width:18px;left:3px;top:3px;background:#fff;border-radius:50%;transition:.2s;box-shadow:0 1px 2px rgba(0,0,0,.3);}',
            '.sswa-switch input:checked + .sswa-slider{background:var(--accent);}',
            '.sswa-switch input:checked + .sswa-slider:before{transform:translateX(20px);}',
            '.sswa-select,.sswa-input,.sswa-textarea{font:inherit;color:var(--text);background:var(--input-bg);border:1px solid var(--input-bd);border-radius:8px;padding:7px 9px;}',
            '.sswa-select{min-width:84px;}',
            '.sswa-textarea{width:210px;min-height:36px;resize:vertical;font-family:monospace;font-size:12px;}',
            '.sswa-btn{font:inherit;font-weight:600;font-size:13px;cursor:pointer;border-radius:8px;padding:7px 14px;border:1px solid var(--input-bd);background:transparent;color:var(--text);transition:all .15s;}',
            '.sswa-btn:hover{border-color:var(--accent);color:var(--accent);}',
            '.sswa-btn-primary{background:var(--accent);border-color:var(--accent);color:#04130d;}',
            '.sswa-btn-primary:hover{filter:brightness(1.08);color:#04130d;}',
            '.sswa-btn-sm{padding:5px 10px;font-size:12px;}',
            '.sswa-actions{display:flex;gap:7px;align-items:center;white-space:nowrap;}',
            '.sswa-stat{font-size:12px;margin-left:2px;}',
            '.sswa-stat.ok{color:var(--accent);} .sswa-stat.err{color:#dc2626;} .sswa-stat.busy{color:var(--muted);}',
            '.sswa-overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:99999;display:flex;align-items:center;justify-content:center;padding:16px;}',
            '.sswa-modal{background:var(--card);color:var(--text);border:1px solid var(--border);border-radius:14px;max-width:680px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.4);}',
            '.sswa-modal-h{padding:14px 18px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;}',
            '.sswa-modal-h h4{margin:0;font-size:15px;}',
            '.sswa-modal-b{padding:18px;}',
            '.sswa-modal-b p{margin:0 0 12px;color:var(--muted);font-size:13.5px;}',
            '.sswa-code{width:100%;font-family:monospace;font-size:12.5px;color:var(--text);background:var(--input-bg);border:1px solid var(--input-bd);border-radius:8px;padding:10px;}',
            '.sswa-modal-f{padding:12px 18px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:8px;}'
        ].join('');
        var s = document.createElement('style');
        s.id = STYLE_ID;
        s.appendChild(document.createTextNode(css));
        (document.head || document.documentElement).appendChild(s);
    }

    function el(tag, cls, text) {
        var n = document.createElement(tag);
        if (cls) { n.className = cls; }
        if (text != null) { n.textContent = String(text); }
        return n;
    }

    function App(root) {
        this.root = root;
        this.base = (root.getAttribute('data-base') || '').replace(/\/$/, '');
        this.csrf = root.getAttribute('data-csrf') || '';
        this.theme = (root.getAttribute('data-ui-theme') === 'dark') ? 'dark' : 'light';
        this.showConfig = root.getAttribute('data-config') === '1';
        this.servers = [];
    }

    App.prototype.request = function (method, url, body) {
        var headers = { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
        if (this.csrf) { headers['X-CSRF-TOKEN'] = this.csrf; }
        var opts = { method: method, headers: headers, credentials: 'same-origin' };
        if (body !== undefined) {
            headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(body);
        }
        return fetch(url, opts).then(function (r) {
            if (!r.ok) { throw new Error('HTTP ' + r.status); }
            return r.json();
        });
    };

    App.prototype.mount = function () {
        this.wrap = el('div', 'sswa sswa-' + this.theme);
        this.root.appendChild(this.wrap);
        if (this.showConfig) { this.renderConfig(); }
        this.tableHost = el('div');
        this.wrap.appendChild(this.tableHost);
        this.loadServers();
    };

    /* ---- admin-only global settings panel ---- */
    App.prototype.renderConfig = function () {
        var self = this;
        var card = el('div', 'sswa-card');
        var row = el('div', 'sswa-set-row');
        var txt = el('div', 'sswa-set-txt');
        txt.appendChild(el('h3', null, 'Client-side management'));
        txt.appendChild(el('p', null, 'Allow server owners to enable and configure the widget for their own servers from the client area (/status-widget).'));

        var sw = el('label', 'sswa-switch');
        var cb = document.createElement('input');
        cb.type = 'checkbox';
        cb.disabled = true;
        sw.appendChild(cb);
        sw.appendChild(el('span', 'sswa-slider'));

        var stat = el('span', 'sswa-stat busy', 'Loading…');
        row.appendChild(txt);
        row.appendChild(stat);
        row.appendChild(sw);
        card.appendChild(row);
        this.wrap.appendChild(card);

        this.request('GET', this.base + '/config').then(function (json) {
            cb.checked = !!(json && json.client_management_enabled);
            cb.disabled = false;
            stat.textContent = '';
            stat.className = 'sswa-stat';
        }).catch(function () {
            stat.textContent = 'Failed to load';
            stat.className = 'sswa-stat err';
        });

        cb.addEventListener('change', function () {
            cb.disabled = true;
            stat.className = 'sswa-stat busy';
            stat.textContent = 'Saving…';
            self.request('PATCH', self.base + '/config', { client_management_enabled: cb.checked })
                .then(function (json) {
                    cb.checked = !!(json && json.client_management_enabled);
                    stat.className = 'sswa-stat ok';
                    stat.textContent = 'Saved';
                    window.setTimeout(function () { if (stat.textContent === 'Saved') { stat.textContent = ''; } }, 2000);
                }).catch(function () {
                    cb.checked = !cb.checked;
                    stat.className = 'sswa-stat err';
                    stat.textContent = 'Failed';
                }).then(function () { cb.disabled = false; });
        });
    };

    /* ---- server table ---- */
    App.prototype.loadServers = function () {
        var self = this;
        this.tableHost.innerHTML = '';
        this.tableHost.appendChild(el('div', 'sswa-msg', 'Loading servers…'));
        this.request('GET', this.base + '/data').then(function (json) {
            self.servers = (json && json.data) ? json.data : [];
            self.renderTable();
        }).catch(function () {
            self.tableHost.innerHTML = '';
            self.tableHost.appendChild(el('div', 'sswa-msg err', 'Failed to load servers. Reload the page and try again.'));
        });
    };

    App.prototype.renderTable = function () {
        var self = this;
        this.tableHost.innerHTML = '';
        if (!this.servers.length) {
            this.tableHost.appendChild(el('div', 'sswa-msg', 'You have no servers to configure yet.'));
            return;
        }
        var wrap = el('div', 'sswa-tablewrap');
        var table = el('table', 'sswa-table');
        var thead = document.createElement('thead');
        var htr = document.createElement('tr');
        ['Server', 'Short ID', 'Enabled', 'Theme', 'Allowed domains', ''].forEach(function (h) {
            htr.appendChild(el('th', null, h));
        });
        thead.appendChild(htr);
        table.appendChild(thead);
        var tbody = document.createElement('tbody');
        this.servers.forEach(function (s) { tbody.appendChild(self.renderRow(s)); });
        table.appendChild(tbody);
        wrap.appendChild(table);
        this.tableHost.appendChild(wrap);
    };

    App.prototype.renderRow = function (server) {
        var self = this;
        var tr = document.createElement('tr');

        var tdName = document.createElement('td');
        tdName.appendChild(el('div', 'sswa-name', server.name));
        if (server.node) { tdName.appendChild(el('div', 'sswa-sub', server.node)); }
        tr.appendChild(tdName);

        var tdId = document.createElement('td');
        tdId.appendChild(el('span', 'sswa-mono', server.uuidShort));
        tr.appendChild(tdId);

        var tdEn = document.createElement('td');
        var sw = el('label', 'sswa-switch');
        var cb = document.createElement('input');
        cb.type = 'checkbox';
        cb.checked = !!server.enabled;
        sw.appendChild(cb);
        sw.appendChild(el('span', 'sswa-slider'));
        tdEn.appendChild(sw);
        tr.appendChild(tdEn);

        var tdTheme = document.createElement('td');
        var sel = el('select', 'sswa-select');
        [['dark', 'Dark'], ['light', 'Light']].forEach(function (o) {
            var op = document.createElement('option');
            op.value = o[0]; op.textContent = o[1];
            if (server.theme === o[0]) { op.selected = true; }
            sel.appendChild(op);
        });
        tdTheme.appendChild(sel);
        tr.appendChild(tdTheme);

        var tdDom = document.createElement('td');
        var ta = el('textarea', 'sswa-textarea');
        ta.rows = 2;
        ta.placeholder = 'blank = any site\nexample.com, *.site.net';
        ta.value = server.allowed_domains || '';
        tdDom.appendChild(ta);
        tr.appendChild(tdDom);

        var tdAct = document.createElement('td');
        var act = el('div', 'sswa-actions');
        var embedBtn = el('button', 'sswa-btn sswa-btn-sm', 'Embed');
        var saveBtn = el('button', 'sswa-btn sswa-btn-primary sswa-btn-sm', 'Save');
        var stat = el('span', 'sswa-stat', '');
        act.appendChild(embedBtn);
        act.appendChild(saveBtn);
        act.appendChild(stat);
        tdAct.appendChild(act);
        tr.appendChild(tdAct);

        embedBtn.addEventListener('click', function () { self.openEmbed(server); });

        saveBtn.addEventListener('click', function () {
            saveBtn.disabled = true;
            stat.className = 'sswa-stat busy';
            stat.textContent = 'Saving…';
            var dom = ta.value.trim();
            self.request('PATCH', self.base + '/' + encodeURIComponent(server.id), {
                enabled: cb.checked, theme: sel.value, allowed_domains: dom.length ? dom : null
            }).then(function (saved) {
                server.enabled = !!saved.enabled;
                server.theme = saved.theme;
                server.allowed_domains = saved.allowed_domains;
                stat.className = 'sswa-stat ok';
                stat.textContent = 'Saved';
                window.setTimeout(function () { if (stat.textContent === 'Saved') { stat.textContent = ''; } }, 2200);
            }).catch(function () {
                stat.className = 'sswa-stat err';
                stat.textContent = 'Failed';
            }).then(function () { saveBtn.disabled = false; });
        });

        return tr;
    };

    App.prototype.openEmbed = function (server) {
        var origin = window.location.origin;
        var code = '<script src="' + origin + '/js/status-widget.js" data-server="' +
            server.uuidShort + '" data-theme="' + (server.theme || 'dark') + '" async><\/script>';

        var overlay = el('div', 'sswa-overlay');
        var box = el('div', 'sswa sswa-' + this.theme);
        var modal = el('div', 'sswa-modal');
        var h = el('div', 'sswa-modal-h');
        h.appendChild(el('h4', null, 'Embed code — ' + server.name));
        var x = el('button', 'sswa-btn sswa-btn-sm', '✕');
        h.appendChild(x);
        var b = el('div', 'sswa-modal-b');
        b.appendChild(el('p', null, 'Paste this into any web page. It loads with zero dependencies and refreshes automatically.'));
        var code1 = el('textarea', 'sswa-code');
        code1.rows = 3; code1.readOnly = true; code1.value = code;
        b.appendChild(code1);
        var f = el('div', 'sswa-modal-f');
        var copy = el('button', 'sswa-btn sswa-btn-primary sswa-btn-sm', 'Copy');
        var close = el('button', 'sswa-btn sswa-btn-sm', 'Close');
        f.appendChild(close);
        f.appendChild(copy);
        modal.appendChild(h);
        modal.appendChild(b);
        modal.appendChild(f);
        box.appendChild(modal);
        overlay.appendChild(box);
        document.body.appendChild(overlay);

        function done() { if (overlay.parentNode) { overlay.parentNode.removeChild(overlay); } }
        overlay.addEventListener('click', function (e) { if (e.target === overlay) { done(); } });
        x.addEventListener('click', done);
        close.addEventListener('click', done);
        copy.addEventListener('click', function () {
            code1.focus(); code1.select();
            var ok = false;
            try {
                if (navigator.clipboard && navigator.clipboard.writeText) { navigator.clipboard.writeText(code); ok = true; }
                else { ok = document.execCommand('copy'); }
            } catch (e) { ok = false; }
            copy.textContent = ok ? 'Copied!' : 'Ctrl+C';
            window.setTimeout(function () { copy.textContent = 'Copy'; }, 1800);
        });
        window.setTimeout(function () { code1.focus(); code1.select(); }, 40);
    };

    ready(function () {
        var root = document.getElementById('status-widget-admin-root');
        if (!root) { return; }
        injectStyles();
        new App(root).mount();
    });
})();

