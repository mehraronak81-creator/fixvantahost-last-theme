<?php

namespace Pterodactyl\Console\Commands\Node;

use Illuminate\Console\Command;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Support\Str;
use Pterodactyl\Models\Node;

class RepairNodeTokenCommand extends Command
{
    protected $signature = 'p:node:repair-token
                            {node_id : The ID of the node to repair}
                            {--all : Repair all nodes instead of a single one}';

    protected $description = 'Regenerate daemon_token and daemon_token_id for a node whose encrypted token can no longer be decrypted (MAC invalid).';

    public function __construct(private Encrypter $encrypter)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if ($this->option('all')) {
            return $this->repairAll();
        }

        $nodeId = (int) $this->argument('node_id');
        $node = Node::find($nodeId);

        if (!$node) {
            $this->error("Node #{$nodeId} not found.");
            return 1;
        }

        return $this->repairNode($node);
    }

    private function repairAll(): int
    {
        $nodes = Node::all();

        if ($nodes->isEmpty()) {
            $this->warn('No nodes found in the database.');
            return 0;
        }

        $this->info("Found {$nodes->count()} node(s). Checking each...");
        $repaired = 0;

        foreach ($nodes as $node) {
            if ($this->needsRepair($node)) {
                $this->warn("Node #{$node->id} ({$node->name}) — token is corrupt, repairing...");
                $this->repairNode($node);
                $repaired++;
            } else {
                $this->info("Node #{$node->id} ({$node->name}) — token is OK, skipping.");
            }
        }

        $this->newLine();
        $this->info("Done. Repaired {$repaired} of {$nodes->count()} node(s).");
        return 0;
    }

    private function needsRepair(Node $node): bool
    {
        try {
            $this->encrypter->decrypt($node->daemon_token);
            return false;
        } catch (\Exception) {
            return true;
        }
    }

    private function repairNode(Node $node): int
    {
        $plainToken = Str::random(Node::DAEMON_TOKEN_LENGTH);
        $tokenId = Str::random(Node::DAEMON_TOKEN_ID_LENGTH);

        $node->daemon_token = $this->encrypter->encrypt($plainToken);
        $node->daemon_token_id = $tokenId;
        $node->save();

        $this->newLine();
        $this->info("✔ Node #{$node->id} ({$node->name}) repaired successfully.");
        $this->newLine();

        $this->table(
            ['Field', 'Value'],
            [
                ['Node ID', $node->id],
                ['Node Name', $node->name],
                ['New Token ID', $tokenId],
                ['New Plaintext Token', $plainToken],
            ]
        );

        $this->newLine();
        $this->warn('⚠  You MUST update this node\'s Wings config.yml with the new token:');
        $this->line("   token: {$plainToken}");
        $this->line("   token_id: {$tokenId}");
        $this->newLine();

        return 0;
    }
}
