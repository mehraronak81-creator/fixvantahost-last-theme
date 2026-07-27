<?php

namespace Pterodactyl\Models;

/**
 * A single point-in-time status sample for a server, used to build the
 * 24 hour sparkline shown by the public Server Status Widget.
 *
 * @property int $id
 * @property int $server_id
 * @property \Carbon\CarbonImmutable $pinged_at
 * @property bool $online
 * @property int|null $player_count
 * @property int|null $ping_ms
 *
 * @property \Pterodactyl\Models\Server $server
 */
class ServerStatusHistory extends Model
{
    /**
     * The resource name for this model when it is transformed into an
     * API representation using fractal.
     */
    public const RESOURCE_NAME = 'server_status_history';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'server_status_history';

    /**
     * This table does not track created_at / updated_at timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'server_id',
        'pinged_at',
        'online',
        'player_count',
        'ping_ms',
    ];

    /**
     * Cast values to the correct type.
     *
     * @var string[]
     */
    protected $casts = [
        'server_id' => 'integer',
        'online' => 'boolean',
        'pinged_at' => 'datetime',
        'player_count' => 'integer',
        'ping_ms' => 'integer',
    ];

    /**
     * Validation rules to apply to this model.
     *
     * @var array<string, string>
     */
    public static array $validationRules = [
        'server_id' => 'required|integer|exists:servers,id',
        'pinged_at' => 'required|date',
        'online' => 'required|boolean',
        'player_count' => 'nullable|integer',
        'ping_ms' => 'nullable|integer',
    ];

    /**
     * The server this status sample belongs to.
     */
    public function server(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}

