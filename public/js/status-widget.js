/*!
 * Server Status Widget - standalone embeddable widget for Pterodactyl
 *
 * Zero dependencies. Self-contained vanilla JavaScript.
 *
 * Usage:
 *   <script src="https://panel.example.com/js/status-widget.js"
 *           data-server="abcd1234"
 *           data-theme="dark"
 *           async></script>
 *
 * The widget renders inline where the <script> tag is located. Multiple
 * independent instances may live on the same page without interfering with
 * one another. All server-provided text is inserted with textContent only,
 * so it can never inject markup. The widget never throws.
 */
(function () {
    'use strict';

    // Hard guard: if the host environment is missing essentials, bail silently.
    if (typeof document === 'undefined' || typeof window === 'undefined') {
        return;
    }

    var STYLE_ID = 'ssw-styles';
    var REFRESH_MS = 30000;
    var HISTORY_WINDOW_SECONDS = 24 * 60 * 60;
    var EM_DASH = '—';

    /* ------------------------------------------------------------------ *
     * Scoped CSS. Injected exactly once per document, regardless of how
     * many widget instances are present. Every selector is prefixed with
     * "ssw-" so the host page cannot collide with us and vice versa.
     * ------------------------------------------------------------------ */
    var CSS = [
        '.ssw-card{',
        '  box-sizing:border-box;',
        '  font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;',
        '  -webkit-font-smoothing:antialiased;',
        '  position:relative;overflow:hidden;',
        '  max-width:340px;width:100%;',
        '  border-radius:16px;',
        '  padding:18px 20px 15px;',
        '  border:1px solid var(--ssw-border);',
        '  background:var(--ssw-bg-grad),var(--ssw-bg);',
        '  color:var(--ssw-text);',
        '  line-height:1.4;',
        '  box-shadow:var(--ssw-shadow);',
        '  transition:transform .25s ease,box-shadow .25s ease;',
        '}',
        '.ssw-card:hover{transform:translateY(-2px);box-shadow:var(--ssw-shadow-hover);}',
        '.ssw-card *{box-sizing:border-box;}',
        '.ssw-card::before{',
        '  content:"";position:absolute;left:0;top:0;right:0;height:3px;',
        '  background:linear-gradient(90deg,transparent,var(--ssw-accent),transparent);',
        '  opacity:0;transition:opacity .3s ease;',
        '}',
        '.ssw-card.ssw-is-online::before{opacity:.85;}',
        '.ssw-head{display:flex;align-items:center;gap:10px;min-width:0;}',
        '.ssw-dot{',
        '  flex:0 0 auto;width:9px;height:9px;border-radius:50%;',
        '  background:var(--ssw-muted);position:relative;',
        '}',
        '.ssw-dot.ssw-on{background:var(--ssw-online);}',
        '.ssw-dot.ssw-off{background:var(--ssw-offline);}',
        '.ssw-dot.ssw-on::after{',
        '  content:"";position:absolute;inset:-4px;border-radius:50%;',
        '  border:2px solid var(--ssw-online);animation:ssw-pulse 2s ease-out infinite;',
        '}',
        '@keyframes ssw-pulse{0%{transform:scale(.55);opacity:.7;}100%{transform:scale(1.7);opacity:0;}}',
        '.ssw-name{',
        '  font-size:15px;font-weight:700;letter-spacing:-.01em;',
        '  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;',
        '  min-width:0;flex:1 1 auto;',
        '}',
        '.ssw-pill{',
        '  flex:0 0 auto;font-size:10px;font-weight:700;letter-spacing:.06em;',
        '  text-transform:uppercase;padding:3px 9px;border-radius:999px;',
        '  color:var(--ssw-muted);background:var(--ssw-pill-bg);white-space:nowrap;',
        '}',
        '.ssw-pill.ssw-on{color:var(--ssw-online);background:var(--ssw-online-bg);}',
        '.ssw-pill.ssw-off{color:var(--ssw-offline);background:var(--ssw-offline-bg);}',
        '.ssw-stats{display:flex;gap:14px;margin-top:16px;}',
        '.ssw-stat{flex:1 1 0;min-width:0;}',
        '.ssw-stat-label{',
        '  display:block;font-size:9px;letter-spacing:.08em;text-transform:uppercase;',
        '  color:var(--ssw-muted);margin-bottom:3px;',
        '}',
        '.ssw-stat-value{',
        '  font-size:18px;font-weight:700;letter-spacing:-.02em;line-height:1.1;',
        '  color:var(--ssw-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;',
        '}',
        '.ssw-stat-value .ssw-unit{font-size:11px;font-weight:600;color:var(--ssw-muted);margin-left:2px;}',
        '.ssw-ping.ssw-good{color:var(--ssw-online);}',
        '.ssw-ping.ssw-ok{color:#f59e0b;}',
        '.ssw-ping.ssw-bad{color:var(--ssw-offline);}',
        '.ssw-bar{margin-top:7px;height:4px;border-radius:999px;background:var(--ssw-track);overflow:hidden;}',
        '.ssw-bar-fill{height:100%;width:0;border-radius:999px;background:var(--ssw-accent);transition:width .45s ease;}',
        '.ssw-meta{margin-top:14px;font-size:11px;color:var(--ssw-muted);}',
        '.ssw-meta-line{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}',
        '.ssw-meta-line+.ssw-meta-line{margin-top:2px;}',
        '.ssw-spark-wrap{margin-top:16px;}',
        '.ssw-spark-head{',
        '  display:flex;justify-content:space-between;align-items:baseline;',
        '  font-size:9px;letter-spacing:.07em;text-transform:uppercase;',
        '  color:var(--ssw-muted);margin-bottom:6px;',
        '}',
        '.ssw-spark-now{font-weight:700;color:var(--ssw-text);letter-spacing:0;}',
        '.ssw-canvas{display:block;width:100%;height:46px;}',
        '.ssw-foot{',
        '  margin-top:13px;padding-top:11px;border-top:1px solid var(--ssw-border);',
        '  display:flex;justify-content:space-between;align-items:center;',
        '  font-size:10px;color:var(--ssw-muted);',
        '}',
        '.ssw-foot-brand{display:flex;align-items:center;gap:5px;opacity:.9;}',
        '.ssw-foot-brand::before{content:"";width:5px;height:5px;border-radius:50%;background:var(--ssw-muted);}',
        '.ssw-card.ssw-is-online .ssw-foot-brand::before{background:var(--ssw-online);}',
        '.ssw-error{margin-top:10px;font-size:12px;color:var(--ssw-muted);}',
        '.ssw-card.ssw-loading .ssw-name{opacity:.5;}',
        '@media (prefers-reduced-motion:reduce){',
        '  .ssw-card{transition:none;}',
        '  .ssw-dot.ssw-on::after{animation:none;}',
        '  .ssw-bar-fill{transition:none;}',
        '}'
    ].join('\n');

    /* Per-theme CSS custom properties applied inline on the card element. */
    var THEMES = {
        dark: {
            '--ssw-bg': '#0d1117',
            '--ssw-bg-grad': 'radial-gradient(135% 95% at 100% -10%, rgba(16,185,129,0.10), rgba(16,185,129,0) 55%)',
            '--ssw-text': '#e4f0ec',
            '--ssw-muted': '#8b97a3',
            '--ssw-border': 'rgba(255,255,255,0.08)',
            '--ssw-accent': '#10b981',
            '--ssw-online': '#10b981',
            '--ssw-offline': '#ef4444',
            '--ssw-online-bg': 'rgba(16,185,129,0.15)',
            '--ssw-offline-bg': 'rgba(239,68,68,0.15)',
            '--ssw-pill-bg': 'rgba(255,255,255,0.06)',
            '--ssw-track': 'rgba(255,255,255,0.09)',
            '--ssw-shadow': '0 4px 22px rgba(0,0,0,0.38)',
            '--ssw-shadow-hover': '0 12px 34px rgba(0,0,0,0.48)',
            sparkLine: '#10b981',
            sparkFillTop: 'rgba(16,185,129,0.32)',
            sparkFillBottom: 'rgba(16,185,129,0.02)',
            sparkDim: '#3a4754',
            sparkGrid: 'rgba(139,151,163,0.12)'
        },
        light: {
            '--ssw-bg': '#ffffff',
            '--ssw-bg-grad': 'radial-gradient(135% 95% at 100% -10%, rgba(16,185,129,0.08), rgba(16,185,129,0) 55%)',
            '--ssw-text': '#0f172a',
            '--ssw-muted': '#64748b',
            '--ssw-border': 'rgba(15,23,42,0.09)',
            '--ssw-accent': '#10b981',
            '--ssw-online': '#059669',
            '--ssw-offline': '#dc2626',
            '--ssw-online-bg': 'rgba(16,185,129,0.13)',
            '--ssw-offline-bg': 'rgba(220,38,38,0.10)',
            '--ssw-pill-bg': 'rgba(15,23,42,0.05)',
            '--ssw-track': 'rgba(15,23,42,0.08)',
            '--ssw-shadow': '0 4px 18px rgba(15,23,42,0.09)',
            '--ssw-shadow-hover': '0 12px 30px rgba(15,23,42,0.15)',
            sparkLine: '#059669',
            sparkFillTop: 'rgba(5,150,105,0.24)',
            sparkFillBottom: 'rgba(5,150,105,0.02)',
            sparkDim: '#cbd5e1',
            sparkGrid: 'rgba(100,116,139,0.10)'
        }
    };

    /* ------------------------------------------------------------------ *
     * Helpers
     * ------------------------------------------------------------------ */

    function injectStylesOnce() {
        try {
            if (document.getElementById(STYLE_ID)) {
                return;
            }
            var style = document.createElement('style');
            style.id = STYLE_ID;
            style.type = 'text/css';
            style.appendChild(document.createTextNode(CSS));
            var head = document.head || document.getElementsByTagName('head')[0] || document.documentElement;
            head.appendChild(style);
        } catch (e) {
            /* styling is non-fatal */
        }
    }

    function normalizeTheme(value) {
        return value === 'light' ? 'light' : 'dark';
    }

    /**
     * Determine the origin that served this widget script so we can build
     * the API URL relative to the panel rather than the embedding page.
     */
    function resolveScriptOrigin(scriptEl) {
        var src = null;
        try {
            if (scriptEl && scriptEl.src) {
                src = scriptEl.src;
            }
        } catch (e) {
            src = null;
        }
        if (!src) {
            // Fallback: scan for any script whose src references this file.
            try {
                var scripts = document.getElementsByTagName('script');
                for (var i = 0; i < scripts.length; i++) {
                    var s = scripts[i].src || '';
                    if (s.indexOf('status-widget.js') !== -1) {
                        src = s;
                        break;
                    }
                }
            } catch (e2) {
                src = null;
            }
        }
        if (src) {
            try {
                return new URL(src, window.location.href).origin;
            } catch (e3) {
                // Manual parse fallback for ancient engines.
                var m = /^(https?:\/\/[^/]+)/i.exec(src);
                if (m) {
                    return m[1];
                }
            }
        }
        // Last resort: same origin as the page.
        return window.location.origin;
    }

    /** Create an element with an optional class and text. */
    function el(tag, className, text) {
        var node = document.createElement(tag);
        if (className) {
            node.className = className;
        }
        if (text != null) {
            node.textContent = String(text);
        }
        return node;
    }

    /** Coerce to a finite integer or return null. */
    function toIntOrNull(value) {
        if (value === null || value === undefined) {
            return null;
        }
        var n = Number(value);
        if (!isFinite(n)) {
            return null;
        }
        return Math.round(n);
    }

    /** Safe string or null. */
    function toStringOrNull(value) {
        if (value === null || value === undefined) {
            return null;
        }
        var s = String(value);
        return s.length ? s : null;
    }

    function fmtCount(count, max) {
        var c = count == null ? EM_DASH : String(count);
        var m = max == null ? EM_DASH : String(max);
        return c + '/' + m;
    }

    /* ------------------------------------------------------------------ *
     * Widget instance
     * ------------------------------------------------------------------ */
    function Widget(scriptEl) {
        this.scriptEl = scriptEl;
        this.server = '';
        this.theme = 'dark';
        this.origin = '';
        this.timer = null;
        this.destroyed = false;
        this.inFlight = false;
        this.lastPayload = null;

        // DOM handles, created in build().
        this.root = null;
        this.dot = null;
        this.nameEl = null;
        this.statusEl = null;
        this.playersValue = null;
        this.barFill = null;
        this.peakValue = null;
        this.sparkNowEl = null;
        this.metaEl = null;
        this.canvas = null;
        this.errorEl = null;
        this.updatedEl = null;
    }

    Widget.prototype.readConfig = function () {
        var s = this.scriptEl;
        var server = '';
        var themeAttr = '';
        try {
            server = (s.getAttribute('data-server') || '').trim();
            themeAttr = (s.getAttribute('data-theme') || '').trim().toLowerCase();
        } catch (e) {
            /* ignore */
        }
        this.server = server;
        // An explicit data-theme on the script tag always wins. We only fall
        // back to the server-configured theme when the embed omits it.
        this.themeExplicit = (themeAttr === 'dark' || themeAttr === 'light');
        this.theme = normalizeTheme(themeAttr);
        this.origin = resolveScriptOrigin(s);
        return server.length > 0;
    };

    Widget.prototype.applyThemeVars = function (node) {
        var t = THEMES[this.theme] || THEMES.dark;
        for (var key in t) {
            if (Object.prototype.hasOwnProperty.call(t, key) && key.indexOf('--') === 0) {
                try {
                    node.style.setProperty(key, t[key]);
                } catch (e) {
                    /* ignore */
                }
            }
        }
    };

    /** Build the static DOM skeleton and insert it where the script lives. */
    Widget.prototype.build = function () {
        var root = el('div', 'ssw-card ssw-loading');
        root.setAttribute('role', 'status');
        root.setAttribute('aria-live', 'polite');
        this.applyThemeVars(root);

        // Header: dot + name + status pill
        var head = el('div', 'ssw-head');
        this.dot = el('span', 'ssw-dot');
        this.nameEl = el('span', 'ssw-name', 'Loading' + EM_DASH);
        this.statusEl = el('span', 'ssw-pill', '');
        head.appendChild(this.dot);
        head.appendChild(this.nameEl);
        head.appendChild(this.statusEl);
        root.appendChild(head);

        // Stats: players (with capacity bar) + ping
        var stats = el('div', 'ssw-stats');

        var playersStat = el('div', 'ssw-stat');
        playersStat.appendChild(el('span', 'ssw-stat-label', 'Players'));
        this.playersValue = el('span', 'ssw-stat-value', EM_DASH);
        playersStat.appendChild(this.playersValue);
        var bar = el('div', 'ssw-bar');
        this.barFill = el('div', 'ssw-bar-fill');
        bar.appendChild(this.barFill);
        playersStat.appendChild(bar);

        var peakStat = el('div', 'ssw-stat');
        peakStat.appendChild(el('span', 'ssw-stat-label', 'Peak · 24h'));
        this.peakValue = el('span', 'ssw-stat-value', EM_DASH);
        peakStat.appendChild(this.peakValue);

        stats.appendChild(playersStat);
        stats.appendChild(peakStat);
        root.appendChild(stats);

        // Optional version / MOTD meta line(s)
        this.metaEl = el('div', 'ssw-meta');
        this.metaEl.style.display = 'none';
        root.appendChild(this.metaEl);

        // Sparkline with a live current-ping readout
        var sparkWrap = el('div', 'ssw-spark-wrap');
        var sparkHead = el('div', 'ssw-spark-head');
        sparkHead.appendChild(el('span', null, 'Players · 24h'));
        this.sparkNowEl = el('span', 'ssw-spark-now', '');
        sparkHead.appendChild(this.sparkNowEl);
        sparkWrap.appendChild(sparkHead);
        this.canvas = el('canvas', 'ssw-canvas');
        sparkWrap.appendChild(this.canvas);
        root.appendChild(sparkWrap);

        // Footer: last-updated stamp + brand
        var foot = el('div', 'ssw-foot');
        this.updatedEl = el('span', null, '');
        foot.appendChild(this.updatedEl);
        foot.appendChild(el('span', 'ssw-foot-brand', 'Server Status'));
        root.appendChild(foot);

        // Error line (hidden until needed)
        this.errorEl = el('div', 'ssw-error');
        this.errorEl.style.display = 'none';
        root.appendChild(this.errorEl);

        this.root = root;

        // Insert the card immediately after the script tag so it appears
        // exactly where the embedder placed it.
        try {
            var parent = this.scriptEl.parentNode;
            if (parent) {
                if (this.scriptEl.nextSibling) {
                    parent.insertBefore(root, this.scriptEl.nextSibling);
                } else {
                    parent.appendChild(root);
                }
            } else {
                (document.body || document.documentElement).appendChild(root);
            }
        } catch (e) {
            try {
                (document.body || document.documentElement).appendChild(root);
            } catch (e2) {
                /* nothing else we can do */
            }
        }
    };

    Widget.prototype.apiUrl = function () {
        return this.origin + '/api/status-widget/' + encodeURIComponent(this.server);
    };

    /** Fetch current status and update the view. Never throws. */
    Widget.prototype.refresh = function () {
        if (this.destroyed || this.inFlight) {
            return;
        }
        var self = this;
        this.inFlight = true;

        var url = this.apiUrl();

        // Prefer fetch; fall back to XHR if fetch is unavailable.
        if (typeof window.fetch === 'function') {
            var opts = { method: 'GET', credentials: 'omit', cache: 'no-store' };
            window.fetch(url, opts).then(function (resp) {
                if (!resp) {
                    throw new Error('no response');
                }
                if (resp.status === 404) {
                    return resp.json().then(function () {
                        return { __notFound: true };
                    }, function () {
                        return { __notFound: true };
                    });
                }
                if (!resp.ok) {
                    throw new Error('http ' + resp.status);
                }
                return resp.json();
            }).then(function (data) {
                self.inFlight = false;
                if (self.destroyed) {
                    return;
                }
                if (data && data.__notFound) {
                    self.renderNotFound();
                } else {
                    self.renderPayload(data);
                }
            }).catch(function () {
                self.inFlight = false;
                if (!self.destroyed) {
                    self.renderError();
                }
            });
        } else {
            this.refreshXhr(url);
        }
    };

    /** Legacy XHR path for environments without fetch. */
    Widget.prototype.refreshXhr = function (url) {
        var self = this;
        var xhr;
        try {
            xhr = new XMLHttpRequest();
        } catch (e) {
            self.inFlight = false;
            self.renderError();
            return;
        }
        try {
            xhr.open('GET', url, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState !== 4) {
                    return;
                }
                self.inFlight = false;
                if (self.destroyed) {
                    return;
                }
                if (xhr.status === 404) {
                    self.renderNotFound();
                    return;
                }
                if (xhr.status < 200 || xhr.status >= 300) {
                    self.renderError();
                    return;
                }
                var data = null;
                try {
                    data = JSON.parse(xhr.responseText);
                } catch (e2) {
                    self.renderError();
                    return;
                }
                self.renderPayload(data);
            };
            xhr.onerror = function () {
                self.inFlight = false;
                if (!self.destroyed) {
                    self.renderError();
                }
            };
            xhr.send();
        } catch (e3) {
            self.inFlight = false;
            self.renderError();
        }
    };

    /** Render the success payload defensively. */
    Widget.prototype.renderPayload = function (data) {
        try {
            if (!data || typeof data !== 'object') {
                this.renderError();
                return;
            }
            this.lastPayload = data;
            this.root.classList.remove('ssw-loading');
            this.errorEl.style.display = 'none';

            var online = data.online === true;
            var name = toStringOrNull(data.name) || 'Unknown server';
            var playerCount = toIntOrNull(data.player_count);
            var maxPlayers = toIntOrNull(data.max_players);
            var pingMs = toIntOrNull(data.ping_ms);
            var version = toStringOrNull(data.version);
            var motd = toStringOrNull(data.motd);

            // Fall back to the server-configured theme ONLY when the embed did
            // not set an explicit data-theme. An explicit data-theme always wins,
            // so multiple embeds of the same server can use different themes.
            if (!this.themeExplicit) {
                var srvTheme = toStringOrNull(data.theme);
                if (srvTheme && (srvTheme === 'dark' || srvTheme === 'light') && srvTheme !== this.theme) {
                    this.theme = srvTheme;
                    this.applyThemeVars(this.root);
                }
            }

            // Online state drives the accent line + pulse.
            if (online) {
                this.root.classList.add('ssw-is-online');
            } else {
                this.root.classList.remove('ssw-is-online');
            }

            // Dot + status pill
            this.dot.className = 'ssw-dot ' + (online ? 'ssw-on' : 'ssw-off');
            this.statusEl.className = 'ssw-pill ' + (online ? 'ssw-on' : 'ssw-off');
            this.statusEl.textContent = online ? 'Online' : 'Offline';

            // Name (textContent only — never innerHTML)
            this.nameEl.textContent = name;
            this.nameEl.setAttribute('title', name);

            // Players value + capacity bar
            this.playersValue.textContent = fmtCount(playerCount, maxPlayers);
            var pct = 0;
            if (online && playerCount != null && maxPlayers != null && maxPlayers > 0) {
                pct = Math.max(0, Math.min(100, (playerCount / maxPlayers) * 100));
            }
            this.barFill.style.width = pct + '%';

            // Peak players over the last 24h (computed from history)
            var historyArr = Array.isArray(data.history) ? data.history : [];
            var peak = this.computePeak(historyArr);
            this.peakValue.textContent = (peak == null) ? EM_DASH : String(peak);

            // Live readout in the graph header = current players online
            this.sparkNowEl.textContent = (online && playerCount != null)
                ? (playerCount + ' online')
                : EM_DASH;

            // Meta (version / motd), text nodes only
            this.renderMeta(version, motd);

            // Updated timestamp
            this.updatedEl.textContent = this.formatUpdated(data.updated_at);

            // Sparkline
            this.drawSparkline(Array.isArray(data.history) ? data.history : []);
        } catch (e) {
            this.renderError();
        }
    };

    /** Highest player count seen across the 24h history (null if no data). */
    Widget.prototype.computePeak = function (history) {
        var peak = null;
        for (var i = 0; i < history.length; i++) {
            var r = history[i];
            if (!r || typeof r !== 'object' || r.online !== true) {
                continue;
            }
            var p = toIntOrNull(r.players);
            if (p != null && (peak == null || p > peak)) {
                peak = p;
            }
        }
        return peak;
    };

    Widget.prototype.renderMeta = function (version, motd) {
        try {
            // Clear existing children safely.
            while (this.metaEl.firstChild) {
                this.metaEl.removeChild(this.metaEl.firstChild);
            }
            var any = false;
            if (version) {
                var v = el('div', 'ssw-meta-line');
                v.appendChild(document.createTextNode(version));
                v.setAttribute('title', version);
                this.metaEl.appendChild(v);
                any = true;
            }
            if (motd) {
                var m = el('div', 'ssw-meta-line');
                m.appendChild(document.createTextNode(motd));
                m.setAttribute('title', motd);
                this.metaEl.appendChild(m);
                any = true;
            }
            this.metaEl.style.display = any ? 'block' : 'none';
        } catch (e) {
            this.metaEl.style.display = 'none';
        }
    };

    Widget.prototype.formatUpdated = function (iso) {
        try {
            var d = iso ? new Date(iso) : new Date();
            if (isNaN(d.getTime())) {
                d = new Date();
            }
            var h = d.getHours();
            var min = d.getMinutes();
            var hh = h < 10 ? '0' + h : '' + h;
            var mm = min < 10 ? '0' + min : '' + min;
            return 'Updated ' + hh + ':' + mm;
        } catch (e) {
            return '';
        }
    };

    /** The server exists logically but is disabled / unknown. */
    Widget.prototype.renderNotFound = function () {
        try {
            this.root.classList.remove('ssw-loading');
            this.root.classList.remove('ssw-is-online');
            this.lastPayload = null;
            this.dot.className = 'ssw-dot ssw-off';
            this.statusEl.className = 'ssw-pill ssw-off';
            this.statusEl.textContent = 'Offline';
            this.nameEl.textContent = 'Status unavailable';
            this.nameEl.removeAttribute('title');
            this.playersValue.textContent = EM_DASH;
            this.barFill.style.width = '0%';
            this.peakValue.textContent = EM_DASH;
            this.sparkNowEl.textContent = EM_DASH;
            this.metaEl.style.display = 'none';
            this.updatedEl.textContent = '';
            this.errorEl.style.display = 'block';
            this.errorEl.textContent = 'This status widget is not available.';
            this.drawSparkline([]);
        } catch (e) {
            /* never throw */
        }
    };

    /** Network / parse failure. Keep the last good data if we have it. */
    Widget.prototype.renderError = function () {
        try {
            this.root.classList.remove('ssw-loading');
            if (this.lastPayload) {
                // Keep showing the stale snapshot but flag the connection issue.
                this.errorEl.style.display = 'block';
                this.errorEl.textContent = 'Could not refresh status.';
                return;
            }
            this.root.classList.remove('ssw-is-online');
            this.dot.className = 'ssw-dot ssw-off';
            this.statusEl.className = 'ssw-pill';
            this.statusEl.textContent = '';
            this.nameEl.textContent = 'Status unavailable';
            this.nameEl.removeAttribute('title');
            this.playersValue.textContent = EM_DASH;
            this.barFill.style.width = '0%';
            this.peakValue.textContent = EM_DASH;
            this.sparkNowEl.textContent = EM_DASH;
            this.metaEl.style.display = 'none';
            this.updatedEl.textContent = '';
            this.errorEl.style.display = 'block';
            this.errorEl.textContent = 'Unable to reach the server.';
            this.drawSparkline([]);
        } catch (e) {
            /* never throw */
        }
    };

    /**
     * Draw the 24h ping sparkline on the canvas. Points are plotted by their
     * timestamp across a fixed 24h window so gaps are spatially accurate.
     * Online points with a numeric ping form the line + fill; offline / null
     * points are drawn as dim markers at the baseline.
     */
    Widget.prototype.drawSparkline = function (history) {
        var canvas = this.canvas;
        if (!canvas) {
            return;
        }
        var ctx;
        try {
            ctx = canvas.getContext('2d');
        } catch (e) {
            return;
        }
        if (!ctx) {
            return;
        }

        var theme = THEMES[this.theme] || THEMES.dark;

        try {
            // Size the backing store to the CSS box * devicePixelRatio.
            var dpr = window.devicePixelRatio || 1;
            var cssWidth = canvas.clientWidth || canvas.offsetWidth || 300;
            var cssHeight = canvas.clientHeight || 42;
            if (cssWidth < 1) {
                cssWidth = 300;
            }
            if (cssHeight < 1) {
                cssHeight = 42;
            }
            var w = Math.max(1, Math.round(cssWidth * dpr));
            var h = Math.max(1, Math.round(cssHeight * dpr));
            if (canvas.width !== w) {
                canvas.width = w;
            }
            if (canvas.height !== h) {
                canvas.height = h;
            }

            ctx.setTransform(1, 0, 0, 1, 0, 0);
            ctx.clearRect(0, 0, w, h);
            ctx.scale(dpr, dpr);

            var padX = 1;
            var padTop = 4;
            var padBottom = 4;
            var plotW = cssWidth - padX * 2;
            var plotH = cssHeight - padTop - padBottom;
            if (plotW <= 0 || plotH <= 0) {
                return;
            }
            var baselineY = padTop + plotH;

            // Baseline grid line.
            ctx.strokeStyle = theme.sparkGrid;
            ctx.lineWidth = 1;
            ctx.beginPath();
            ctx.moveTo(padX, baselineY + 0.5);
            ctx.lineTo(padX + plotW, baselineY + 0.5);
            ctx.stroke();

            // Normalize and filter the history rows.
            var rows = [];
            var nowSec = Math.floor(Date.now() / 1000);
            var windowStart = nowSec - HISTORY_WINDOW_SECONDS;
            var i;
            for (i = 0; i < history.length; i++) {
                var row = history[i];
                if (!row || typeof row !== 'object') {
                    continue;
                }
                var t = toIntOrNull(row.t);
                if (t == null) {
                    continue;
                }
                rows.push({
                    t: t,
                    online: row.online === true,
                    players: toIntOrNull(row.players),
                    ping: toIntOrNull(row.ping_ms)
                });
            }
            rows.sort(function (a, b) { return a.t - b.t; });

            if (!rows.length) {
                return;
            }

            // Time axis bounds: anchor to a full 24h window ending "now",
            // but never start later than the first sample.
            var minT = Math.min(windowStart, rows[0].t);
            var maxT = Math.max(nowSec, rows[rows.length - 1].t);
            var spanT = maxT - minT;
            if (spanT <= 0) {
                spanT = 1;
            }

            // Player-count bounds for the vertical scale (0 sits at the baseline).
            var peak = 0;
            var haveData = false;
            for (i = 0; i < rows.length; i++) {
                if (rows[i].online && rows[i].players != null) {
                    haveData = true;
                    if (rows[i].players > peak) { peak = rows[i].players; }
                }
            }
            if (!haveData) {
                // No usable player data — just dim markers along the baseline.
                this.drawDimMarkers(ctx, rows, theme, padX, plotW, minT, spanT, baselineY);
                return;
            }
            // Headroom so the peak isn't glued to the top; floor of 1 avoids /0.
            var scaleMax = peak > 0 ? peak * 1.15 : 1;

            var self = this;
            function xFor(t) {
                return padX + ((t - minT) / spanT) * plotW;
            }
            function yFor(players) {
                var ratio = players / scaleMax; // 0 = empty, 1 = busiest
                if (ratio < 0) { ratio = 0; }
                if (ratio > 1) { ratio = 1; }
                // More players sit higher on the chart.
                return padTop + (1 - ratio) * plotH;
            }

            // Build the polyline of online points with a numeric player count.
            var linePoints = [];
            for (i = 0; i < rows.length; i++) {
                if (rows[i].online && rows[i].players != null) {
                    linePoints.push({ x: xFor(rows[i].t), y: yFor(rows[i].players) });
                }
            }

            // Filled area under the line — a soft vertical gradient.
            if (linePoints.length >= 2) {
                ctx.beginPath();
                ctx.moveTo(linePoints[0].x, linePoints[0].y);
                for (i = 1; i < linePoints.length; i++) {
                    ctx.lineTo(linePoints[i].x, linePoints[i].y);
                }
                ctx.strokeStyle = theme.sparkLine;
                ctx.lineWidth = 1.75;
                ctx.lineJoin = 'round';
                ctx.lineCap = 'round';
                ctx.stroke();
            } else if (linePoints.length === 1) {
                // Single online point: draw a dot.
                ctx.beginPath();
                ctx.arc(linePoints[0].x, linePoints[0].y, 1.6, 0, Math.PI * 2);
                ctx.fillStyle = theme.sparkLine;
                ctx.fill();
            }

            // Dim markers for offline / null-ping samples.
            this.drawDimMarkers(ctx, rows, theme, padX, plotW, minT, spanT, baselineY);
        } catch (e) {
            /* sparkline failures must never break the widget */
        }
    };

    Widget.prototype.drawDimMarkers = function (ctx, rows, theme, padX, plotW, minT, spanT, baselineY) {
        try {
            ctx.fillStyle = theme.sparkDim;
            for (var i = 0; i < rows.length; i++) {
                var r = rows[i];
                if (r.online && r.players != null) {
                    continue;
                }
                var x = padX + ((r.t - minT) / spanT) * plotW;
                ctx.beginPath();
                ctx.arc(x, baselineY, 1.4, 0, Math.PI * 2);
                ctx.fill();
            }
        } catch (e) {
            /* ignore */
        }
    };

    Widget.prototype.start = function () {
        var self = this;
        this.refresh();
        try {
            this.timer = window.setInterval(function () {
                if (self.destroyed) {
                    return;
                }
                // Pause polling while the tab is hidden to save resources;
                // it resumes on the next interval when visible again.
                if (document.hidden) {
                    return;
                }
                self.refresh();
            }, REFRESH_MS);
        } catch (e) {
            /* interval is best-effort */
        }

        // Redraw the sparkline crisply on resize / DPR change.
        try {
            this._onResize = function () {
                if (self.destroyed || !self.lastPayload) {
                    return;
                }
                self.drawSparkline(
                    Array.isArray(self.lastPayload.history) ? self.lastPayload.history : []
                );
            };
            window.addEventListener('resize', this._onResize, false);
        } catch (e) {
            /* ignore */
        }

        // Refresh immediately when the tab becomes visible again.
        try {
            this._onVisible = function () {
                if (!self.destroyed && !document.hidden) {
                    self.refresh();
                }
            };
            document.addEventListener('visibilitychange', this._onVisible, false);
        } catch (e) {
            /* ignore */
        }
    };

    Widget.prototype.destroy = function () {
        this.destroyed = true;
        try {
            if (this.timer) {
                window.clearInterval(this.timer);
                this.timer = null;
            }
        } catch (e) { /* ignore */ }
        try {
            if (this._onResize) {
                window.removeEventListener('resize', this._onResize, false);
            }
        } catch (e2) { /* ignore */ }
        try {
            if (this._onVisible) {
                document.removeEventListener('visibilitychange', this._onVisible, false);
            }
        } catch (e3) { /* ignore */ }
    };

    /* ------------------------------------------------------------------ *
     * Bootstrap
     * ------------------------------------------------------------------ */

    function bootFromScript(scriptEl) {
        try {
            if (!scriptEl || scriptEl.getAttribute('data-ssw-initialized') === '1') {
                return;
            }
            scriptEl.setAttribute('data-ssw-initialized', '1');

            var widget = new Widget(scriptEl);
            if (!widget.readConfig()) {
                // Missing required data-server attribute: render a soft error
                // so an embedder notices, but never throw.
                injectStylesOnce();
                widget.theme = normalizeTheme((scriptEl.getAttribute('data-theme') || '').toLowerCase());
                widget.build();
                widget.renderError();
                widget.errorEl.textContent = 'Status widget is missing its data-server attribute.';
                return;
            }
            injectStylesOnce();
            widget.build();
            widget.start();
        } catch (e) {
            /* a single broken instance must not affect others or the page */
        }
    }

    function init() {
        // document.currentScript points at us during synchronous execution.
        var current = null;
        try {
            current = document.currentScript;
        } catch (e) {
            current = null;
        }

        if (current && /status-widget\.js(\?|$)/i.test(current.src || '')) {
            bootFromScript(current);
            return;
        }

        // async/defer or bundled: find every status-widget.js script with a
        // data-server attribute that has not yet been initialized.
        try {
            var scripts = document.getElementsByTagName('script');
            var pending = [];
            for (var i = 0; i < scripts.length; i++) {
                var s = scripts[i];
                var src = s.src || '';
                if (src.indexOf('status-widget.js') !== -1 &&
                    s.getAttribute('data-server') &&
                    s.getAttribute('data-ssw-initialized') !== '1') {
                    pending.push(s);
                }
            }
            for (var j = 0; j < pending.length; j++) {
                bootFromScript(pending[j]);
            }
        } catch (e) {
            /* ignore */
        }
    }

    if (document.readyState === 'loading') {
        // We still want currentScript to be valid, so attempt an immediate
        // boot first (covers the synchronous <script> case), then a DOM-ready
        // sweep for async/deferred loads.
        try {
            init();
        } catch (e) { /* ignore */ }
        document.addEventListener('DOMContentLoaded', init, false);
    } else {
        init();
    }
})();

                ctx.moveTo(linePoints[0].x, baselineY);
                for (i = 0; i < linePoints.length; i++) {
                    ctx.lineTo(linePoints[i].x, linePoints[i].y);
                }
                ctx.lineTo(linePoints[linePoints.length - 1].x, baselineY);
                ctx.closePath();
                var grad = ctx.createLinearGradient(0, padTop, 0, baselineY);
                grad.addColorStop(0, theme.sparkFillTop);
                grad.addColorStop(1, theme.sparkFillBottom);
                ctx.fillStyle = grad;
                ctx.fill();
            }

            // The line itself.
            if (linePoints.length >= 2) {
                ctx.beginPath();
