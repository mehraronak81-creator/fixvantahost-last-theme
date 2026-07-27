<?php

namespace Pterodactyl\Services\StatusWidget;

use Illuminate\Database\Eloquent\Collection;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\ServerStatusHistory;

/**
 * Records periodic status samples into server_status_history and prunes the
 * table to a rolling 24-hour window. Driven by the status-widget:poll command.
 */
class PingHistoryService
{
    public function __construct(private MinecraftPingService $ping)
    {
    }

    /**
     * Probe a single server and persist exactly one history row capturing the
     * current online state, player count and ping. Mirrors the probing
     * strategy of ServerStatusService::liveStatus but writes a compact sample.
     */
    public function record(Server $server): ServerStatusHistory
    {
        $online = false;
        $playerCount = null;
        $pingMs = null;

        $allocation = $server->allocation;

        if ($allocation !== null) {
            $host = $allocation->ip_alias ?? $allocation->ip;
            $port = (int) $allocation->port;

            if (!empty($host) && $port > 0) {
                $result = $this->ping->query($host, $port);

                if (is_array($result)) {
                    // Full Minecraft SLP succeeded.
                    $online = true;
                    $playerCount = $result['player_count'];
                    $pingMs = $result['ping_ms'];
                } else {
                    // Generic TCP latency fallback.
                    $latency = $this->ping->tcpProbe($host, $port);
                    if ($latency !== null) {
                        $online = true;
                        $pingMs = $latency;
                    }
                }
            }
        }

        $history = new ServerStatusHistory();
        $history->server_id = $server->id;
        $history->online = $online;
        $history->player_count = $playerCount;
        $history->ping_ms = $pingMs;
        $history->pinged_at = now();
        $history->save();

        return $history;
    }

    /**
     * Delete every history row older than 24 hours. Returns the number of
     * rows removed so callers can report it.
     */
    public function prune(): int
    {
        return ServerStatusHistory::query()
            ->where('pinged_at', '<', now()->subDay())
            ->delete();
    }

    /**
     * Fetch the recent history samples for a server within the given window
     * (default 24h), ordered oldest-to-newest. Optional helper used by
     * consumers that want raw history rows.
     *
     * @return Collection<int, ServerStatusHistory>
     */
    public function recentFor(Server $server, int $hours = 24): Collection
    {
        return ServerStatusHistory::query()
            ->where('server_id', $server->id)
            ->where('pinged_at', '>=', now()->subHours($hours))
            ->orderBy('pinged_at', 'asc')
            ->get();
    }
}

