<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Abuse safeguards
    |--------------------------------------------------------------------------
    |
    | These limits apply per server, in addition to the global client API rate
    | limit in config/http.php. They protect the Panel and Wings control plane
    | from rapid command, power, and file-operation bursts. They do not replace
    | an edge firewall or upstream DDoS protection for game traffic.
    |
    */
    'abuse' => [
        'command_per_minute' => env('VANTAHOST_COMMANDS_PER_MINUTE', 30),
        'power_per_minute' => env('VANTAHOST_POWER_ACTIONS_PER_MINUTE', 10),
        'file_mutations_per_minute' => env('VANTAHOST_FILE_MUTATIONS_PER_MINUTE', 60),
    ],
];
