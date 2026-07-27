<?php

namespace Pterodactyl\Services\StatusWidget;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\ServerStatusHistory;
use Pterodactyl\Models\ServerStatusWidgetSetting;

/**
 * Orchestrates live status probing and assembles the public JSON payload
 * consumed by the standalone widget and the public API controller.
 */
class ServerStatusService
{
    /**
     * Cache TTL (seconds) for the public payload. Matches the widget's own
     * 30s auto-refresh cadence so we never hammer game servers.
     */
    private const CACHE_TTL = 30;

    public function __construct(private MinecraftPingService $ping)
    {
    }

    /**
     * Resolve a server by its public 8-character uuidShort identifier. Returns
     * null when no server matches.
     */
    public function getServerByUuidShort(string $uuidShort): ?Server
    {
        return Server::query()->where('uuidShort', $uuidShort)->first();
    }

    /**
     * Perform a live probe of the server's primary allocation and return the
     * current status fields.
     *
     * Strategy:
     *   1. Attempt a full Minecraft SLP query. If it returns data the server
     *      is online and we use its rich fields.
     *   2. Otherwise fall back to a generic TCP latency probe. A successful
     *      connection counts as online (with ping only, no player/version
     *      detail).
     *   3. If both fail the server is reported offline with null detail.
     *
     * @return array{
     *     online: bool,
     *     player_count: int|null,
     *     max_players: int|null,
     *     ping_ms: int|null,
     *     version: string|null,
     *     motd: string|null
     * }
     */
    public function liveStatus(Server $server): array
    {
        $allocation = $server->allocation;

        // Without a primary allocation there is nothing to probe.
        if ($allocation === null) {
            return $this->offlineStatus();
        }

        $host = $allocation->ip_alias ?? $allocation->ip;
        $port = (int) $allocation->port;

        if (empty($host) || $port <= 0) {
            return $this->offlineStatus();
        }

        // 1) Full Minecraft Server List Ping.
        $result = $this->ping->query($host, $port);
        if (is_array($result)) {
            return [
                'online' => true,
                'player_count' => $result['player_count'],
                'max_players' => $result['max_players'],
                'ping_ms' => $result['ping_ms'],
                'version' => $result['version'] !== '' ? $result['version'] : null,
                'motd' => $result['motd'] !== '' ? $result['motd'] : null,
            ];
        }

        // 2) Generic TCP fallback for non-Minecraft games.
        $latency = $this->ping->tcpProbe($host, $port);
        if ($latency !== null) {
            return [
                'online' => true,
                'player_count' => null,
                'max_players' => null,
                'ping_ms' => $latency,
                'version' => null,
                'motd' => null,
            ];
        }

        // 3) Everything failed -> offline.
        return $this->offlineStatus();
    }

    /**
     * Assemble the complete public JSON payload for a server. This is the
     * authoritative shape shared with the widget and never deviates from it.
     *
     * @return array<string, mixed>
     */
    public function buildPayload(Server $server, ServerStatusWidgetSetting $setting): array
    {
        $status = $this->liveStatus($server);

        return [
            'server' => $server->uuidShort,
            'name' => $server->name,
            'online' => (bool) $status['online'],
            'player_count' => $status['player_count'],
            'max_players' => $status['max_players'],
            'ping_ms' => $status['ping_ms'],
            'version' => $status['version'],
            'motd' => $status['motd'],
            'theme' => $setting->theme === 'light' ? 'light' : 'dark',
            'updated_at' => now()->toIso8601String(),
            'history' => $this->history($server),
        ];
    }

    /**
     * Memoised public payload. Wrapped in a 30s cache keyed by the server's
     * uuidShort so concurrent widget loads share a single live probe.
     *
     * @return array<string, mixed>
     */
    public function cachedPayload(Server $server, ServerStatusWidgetSetting $setting): array
    {
        return Cache::remember(
            'status-widget:public:' . $server->uuidShort,
            self::CACHE_TTL,
            fn (): array => $this->buildPayload($server, $setting),
        );
    }

    /**
     * Build the 24-hour history series, oldest-to-newest, as
     * [{t: <unix seconds>, online: bool, players: int|null, ping_ms: int|null}, ...].
     *
     * Both player count and ping are included so the widget can graph either;
     * the widget currently plots the player-count trend.
     *
     * @return array<int, array{t: int, online: bool, players: int|null, ping_ms: int|null}>
     */
    private function history(Server $server): array
    {
        $since = now()->subDay();

        return ServerStatusHistory::query()
            ->where('server_id', $server->id)
            ->where('pinged_at', '>=', $since)
            ->orderBy('pinged_at', 'asc')
            ->get()
            ->map(function (ServerStatusHistory $row): array {
                /** @var CarbonInterface $pingedAt */
                $pingedAt = $row->pinged_at;

                return [
                    't' => $pingedAt->getTimestamp(),
                    'online' => (bool) $row->online,
                    'players' => $row->player_count !== null ? (int) $row->player_count : null,
                    'ping_ms' => $row->ping_ms !== null ? (int) $row->ping_ms : null,
                ];
            })
            ->all();
    }

    /**
     * The canonical "offline" status shape.
     *
     * @return array{
     *     online: false,
     *     player_count: null,
     *     max_players: null,
     *     ping_ms: null,
     *     version: null,
     *     motd: null
     * }
     */
    private function offlineStatus(): array
    {
        return [
            'online' => false,
            'player_count' => null,
            'max_players' => null,
            'ping_ms' => null,
            'version' => null,
            'motd' => null,
        ];
    }
}

