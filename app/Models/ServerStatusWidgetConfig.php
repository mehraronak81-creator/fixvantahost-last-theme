<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Simple global key/value store for the Server Status Widget addon.
 *
 * @property int         $id
 * @property string      $key
 * @property string|null $value
 */
class ServerStatusWidgetConfig extends Model
{
    public const KEY_CLIENT_MANAGEMENT = 'client_management_enabled';

    protected $table = 'server_status_widget_config';

    protected $fillable = ['key', 'value'];

    /**
     * Read a raw config value, or the supplied default when the key is unset.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        $row = static::query()->where('key', $key)->first();

        return $row ? $row->value : $default;
    }

    /**
     * Read a boolean config value.
     */
    public static function getBool(string $key, bool $default): bool
    {
        $value = static::get($key, $default ? '1' : '0');

        return $value === '1' || $value === 'true';
    }

    /**
     * Create or update a config value.
     */
    public static function set(string $key, string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Whether server owners may manage their own widget from the client area.
     * Defaults to enabled.
     */
    public static function clientManagementEnabled(): bool
    {
        return static::getBool(self::KEY_CLIENT_MANAGEMENT, true);
    }
}

