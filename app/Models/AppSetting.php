<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'is_encrypted',
        'is_public',
        'description',
        'updated_by',
    ];

    protected $casts = [
        'value' => 'json',
        'is_encrypted' => 'boolean',
        'is_public' => 'boolean',
    ];

    // Cache tag para invalidación
    protected const CACHE_KEY = 'app_settings_all';

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // -----------------------------------------------------------------------
    // Static helpers
    // -----------------------------------------------------------------------

    /**
     * Obtiene el valor de una setting (con cache).
     * La key puede ser "group.key" o solo "key".
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = static::allCached();

        return $settings[$key] ?? $default;
    }

    /**
     * Guarda o actualiza una setting e invalida la cache.
     */
    public static function set(string $key, mixed $value, ?int $updatedBy = null): self
    {
        [$group, $shortKey] = static::parseKey($key);

        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'group' => $group,
                'value' => $value,
                'updated_by' => $updatedBy,
            ]
        );

        Cache::forget(static::CACHE_KEY);

        return $setting;
    }

    /**
     * Retorna todas las settings como array key => value (con cache 1 hora).
     */
    public static function allCached(): array
    {
        return Cache::remember(static::CACHE_KEY, 3600, function () {
            return static::all()->pluck('value', 'key')->toArray();
        });
    }

    /**
     * Invalida la cache de settings.
     */
    public static function clearCache(): void
    {
        Cache::forget(static::CACHE_KEY);
    }

    /**
     * Retorna todas las settings de un grupo específico como array key => value.
     */
    public static function group(string $group): array
    {
        return static::where('group', $group)
            ->get()
            ->pluck('value', 'key')
            ->toArray();
    }

    // -----------------------------------------------------------------------
    // Internals
    // -----------------------------------------------------------------------

    /** Parsea "group.key" → ['group', 'key']. Si no tiene punto, group = 'general'. */
    private static function parseKey(string $key): array
    {
        if (str_contains($key, '.')) {
            [$group, $shortKey] = explode('.', $key, 2);

            return [$group, $shortKey];
        }

        return ['general', $key];
    }
}
