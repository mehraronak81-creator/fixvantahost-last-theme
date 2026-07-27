<?php

namespace Pterodactyl\Console\Commands\StatusWidget;

use Throwable;
use Illuminate\Console\Command;
use Pterodactyl\Models\Server;
use Pterodactyl\Services\StatusWidget\PingHistoryService;

/**
 * Polls every server that has the status widget enabled and records a single
 * history sample for each, then prunes samples older than 24 hours.
 *
 * Intended to be scheduled on a short interval (e.g. every minute) so the public
 * widget can render a rolling 24-hour ping sparkline.
 */
class PollServerStatusCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'status-widget:poll';

    /**
     * @var string
     */
    protected $description = 'Probe every status-widget-enabled server, record a history sample, and prune old samples.';

    /**
     * Execute the console command.
     */
    public function handle(PingHistoryService $history): int
    {
        // Join directly against the settings table rather than relying on a
        // relationship being declared on the core Server model (which this
        // addon must not modify). Only enabled widgets are polled.
        $servers = Server::query()
            ->join('server_status_widget_settings', 'server_status_widget_settings.server_id', '=', 'servers.id')
            ->where('server_status_widget_settings.enabled', true)
            ->select('servers.*')
            ->get();

        $recorded = 0;
        $failed = 0;

        foreach ($servers as $server) {
            try {
                $history->record($server);
                $recorded++;
            } catch (Throwable $exception) {
                // One bad server must never abort the whole poll cycle.
                $failed++;
                $this->warn(sprintf(
                    'Failed to poll server %s (#%d): %s',
                    $server->uuidShort,
                    $server->id,
                    $exception->getMessage()
                ));
            }
        }

        $pruned = (int) $history->prune();

        $this->info(sprintf(
            'Status widget poll complete: %d recorded, %d failed, %d old samples pruned.',
            $recorded,
            $failed,
            $pruned
        ));

        return 0;
    }
}

