<?php

namespace Pterodactyl\Http\Middleware\StatusWidget;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Pterodactyl\Models\ServerStatusWidgetSetting;
use Pterodactyl\Services\StatusWidget\ServerStatusService;

/**
 * Applies per-server CORS handling for the public status widget endpoint.
 *
 * Behaviour is driven by the allowed_domains field of the resolved server's
 * widget setting:
 *   - empty/null  => allow any origin ("Access-Control-Allow-Origin: *").
 *   - populated   => only echo the request Origin back when its host matches an
 *                    allowed entry (case-insensitive, scheme/port ignored, with
 *                    optional "*." wildcard support). When it does not match the
 *                    header is omitted so the browser blocks the response.
 *
 * Preflight OPTIONS requests are answered directly with a 204 and never reach
 * the controller.
 */
class WidgetCors
{
    public function __construct(private ServerStatusService $statusService)
    {
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->headers->get('Origin');
        $allowOrigin = $this->resolveAllowOrigin($request, $origin);

        // Answer preflight requests immediately without invoking the controller.
        if ($request->getMethod() === 'OPTIONS') {
            $response = response('', 204);
            $this->applyHeaders($response, $allowOrigin, $origin);

            return $response;
        }

        $response = $next($request);
        $this->applyHeaders($response, $allowOrigin, $origin);

        return $response;
    }

    /**
     * Determine the value for the Access-Control-Allow-Origin header.
     *
     * Returns "*" when all origins are permitted, the exact request Origin when
     * it matches a configured allow-list entry, or null when it should be
     * omitted entirely.
     */
    private function resolveAllowOrigin(Request $request, ?string $origin): ?string
    {
        $uuidShort = (string) $request->route('server');

        $server = $uuidShort !== '' ? $this->statusService->getServerByUuidShort($uuidShort) : null;

        $setting = $server !== null
            ? ServerStatusWidgetSetting::query()->where('server_id', $server->id)->first()
            : null;

        $allowedDomains = $setting?->allowedDomainsArray() ?? [];

        // No restrictions configured (or unknown server): allow any origin.
        if (empty($allowedDomains)) {
            return '*';
        }

        // A restricted setting exists but the request carries no Origin header
        // (e.g. a same-origin or non-browser call): nothing to echo back.
        if ($origin === null || $origin === '') {
            return null;
        }

        $originHost = $this->hostFromOrigin($origin);
        if ($originHost === null) {
            return null;
        }

        foreach ($allowedDomains as $entry) {
            if ($this->originMatches($originHost, $entry)) {
                return $origin;
            }
        }

        return null;
    }

    /**
     * Attach the CORS headers shared by every status-widget response.
     */
    private function applyHeaders(Response $response, ?string $allowOrigin, ?string $origin): void
    {
        $response->headers->set('Access-Control-Allow-Methods', 'GET, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type');

        if ($allowOrigin !== null) {
            $response->headers->set('Access-Control-Allow-Origin', $allowOrigin);

            // When the response varies based on the request Origin, advertise it
            // so shared caches do not serve the wrong origin's response.
            if ($allowOrigin !== '*') {
                $response->headers->set('Vary', 'Origin');
            }
        }
    }

    /**
     * Extract a lower-cased host from an Origin header, ignoring scheme/port.
     */
    private function hostFromOrigin(string $origin): ?string
    {
        $host = parse_url($origin, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            // Fall back to treating the raw value as a bare host:port string.
            $host = preg_replace('#^[a-z][a-z0-9+.-]*://#i', '', $origin);
            $host = explode('/', (string) $host)[0];
            $host = explode(':', $host)[0];
        }

        $host = strtolower(trim((string) $host));

        return $host !== '' ? $host : null;
    }

    /**
     * Test whether an origin host matches a single allow-list entry.
     *
     * Entries may contain a scheme/port which are stripped before comparison,
     * and may begin with "*." to match any subdomain of the given parent host.
     */
    private function originMatches(string $originHost, string $entry): bool
    {
        $entry = strtolower(trim($entry));
        if ($entry === '') {
            return false;
        }

        // Normalise an entry that was written as a full URL down to its host.
        if (str_contains($entry, '://')) {
            $entry = $this->hostFromOrigin($entry) ?? $entry;
        } else {
            $entry = explode('/', $entry)[0];
            $entry = explode(':', $entry)[0];
        }

        if ($entry === '*') {
            return true;
        }

        if (str_starts_with($entry, '*.')) {
            $parent = substr($entry, 2);
            if ($parent === '') {
                return false;
            }

            return $originHost === $parent || str_ends_with($originHost, '.' . $parent);
        }

        return $originHost === $entry;
    }
}

