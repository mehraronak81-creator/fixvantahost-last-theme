<?php

namespace Pterodactyl\Models;

/**
 * Per-server configuration for the public Server Status Widget.
 *
 * @property int $id
 * @property int $server_id
 * @property bool $enabled
 * @property string $theme
 * @property string|null $allowed_domains
 * @property \Carbon\CarbonImmutable $created_at
 * @property \Carbon\CarbonImmutable $updated_at
 *
 * @property \Pterodactyl\Models\Server $server
 */
class ServerStatusWidgetSetting extends Model
{
    /**
     * The resource name for this model when it is transformed into an
     * API representation using fractal.
     */
    public const RESOURCE_NAME = 'server_status_widget_setting';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'server_status_widget_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'server_id',
        'enabled',
        'theme',
        'allowed_domains',
    ];

    /**
     * Cast values to the correct type.
     *
     * @var string[]
     */
    protected $casts = [
        'server_id' => 'integer',
        'enabled' => 'boolean',
    ];

    /**
     * Validation rules to apply to this model.
     *
     * @var array<string, string>
     */
    public static array $validationRules = [
        'server_id' => 'required|integer|exists:servers,id',
        'enabled' => 'sometimes|boolean',
        'theme' => 'sometimes|string|in:dark,light',
        'allowed_domains' => 'nullable|string',
    ];

    /**
     * The server this widget configuration belongs to.
     */
    public function server(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * Return the configured allowed domains as a normalised array.
     *
     * The raw value may contain entries separated by newlines and/or commas.
     * Each entry is trimmed and empty entries are discarded.
     *
     * @return string[]
     */
    public function allowedDomainsArray(): array
    {
        if (empty($this->allowed_domains)) {
            return [];
        }

        $parts = preg_split('/[\r\n,]+/', $this->allowed_domains) ?: [];

        $domains = [];
        foreach ($parts as $part) {
            $trimmed = trim($part);
            if ($trimmed !== '') {
                $domains[] = $trimmed;
            }
        }

        return $domains;
    }
}

