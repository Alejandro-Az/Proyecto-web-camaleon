<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_DRAFT    = 'draft';
    public const STATUS_ACTIVE   = 'active';
    public const STATUS_FINISHED = 'finished';
    public const STATUS_ARCHIVED = 'archived';
    public const STATUS_DELETED  = 'deleted';

    protected $fillable = [
        'type',
        'name',
        'slug',
        'status',
        'event_date',
        'start_time',
        'end_time',
        'theme_key',
        'primary_color',
        'secondary_color',
        'accent_color',
        'font_family',
        'modules',
        'settings',
        'owner_name',
        'owner_email',
        'auto_cleanup_after_days',
    ];

    protected $casts = [
        'event_date'             => 'date',
        'modules'                => 'array',
        'settings'               => 'array',
        'auto_cleanup_after_days'=> 'integer',
    ];

    public function scopePublicVisible(Builder $query): Builder
    {
        return $query->whereIn('status', [
            self::STATUS_ACTIVE,
            self::STATUS_FINISHED,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Módulos (helper + defaults + legacy)
    |--------------------------------------------------------------------------
    */

    public static function moduleDefaults(): array
    {
        return (array) config('event_modules.defaults', []);
    }

    public static function moduleLegacyAliases(): array
    {
        return (array) config('event_modules.legacy_aliases', []);
    }

    /**
     * Devuelve el array de módulos con:
     * - defaults aplicados
     * - aliases legacy mapeados a llaves canónicas
     */
    public function modulesWithDefaults(): array
    {
        $modules = is_array($this->modules) ? $this->modules : [];

        // Si existe legacy pero no existe la llave canónica, lo mapeamos.
        foreach (self::moduleLegacyAliases() as $legacyKey => $canonicalKey) {
            if (!array_key_exists($canonicalKey, $modules) && array_key_exists($legacyKey, $modules)) {
                $modules[$canonicalKey] = (bool) $modules[$legacyKey];
            }
        }

        // Defaults + valores del evento (evento gana)
        return array_replace(self::moduleDefaults(), $modules);
    }

    /**
     * Normaliza un array para guardarse en DB:
     * - aplica defaults
     * - migra legacy → canónico
     * - elimina llaves legacy (para que no sigan propagándose)
     */
    public static function normalizeModulesForStorage(array $modules): array
    {
        $normalized = array_replace(self::moduleDefaults(), $modules);

        foreach (self::moduleLegacyAliases() as $legacyKey => $canonicalKey) {
            if (!array_key_exists($canonicalKey, $modules) && array_key_exists($legacyKey, $modules)) {
                $normalized[$canonicalKey] = (bool) $modules[$legacyKey];
            }
            unset($normalized[$legacyKey]);
        }

        // Asegurar booleanos para llaves conocidas
        foreach (array_keys(self::moduleDefaults()) as $key) {
            $normalized[$key] = (bool) ($normalized[$key] ?? false);
        }

        return $normalized;
    }

    public function isModuleEnabled(string $key, bool $fallback = false): bool
    {
        $modules = $this->modulesWithDefaults();

        if (!array_key_exists($key, $modules)) {
            return $fallback;
        }

        return (bool) $modules[$key];
    }

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function locations()
    {
        return $this->hasMany(EventLocation::class);
    }

    public function guests()
    {
        return $this->hasMany(Guest::class);
    }

    public function songs()
    {
        return $this->hasMany(EventSong::class);
    }

    public function songVotes()
    {
        return $this->hasMany(SongVote::class);
    }

    public function photos()
    {
        return $this->hasMany(EventPhoto::class);
    }

    public function schedules()
    {
        return $this->hasMany(EventSchedule::class);
    }

    public function gifts()
    {
        return $this->hasMany(EventGift::class);
    }

    // Si usted ya tiene estos modelos en su proyecto, deje estas relaciones:
    public function dressCodes()
    {
        return $this->hasMany(EventDressCode::class);
    }

    public function romanticPhrases()
    {
        return $this->hasMany(EventRomanticPhrase::class);
    }

    // Futuro (cuando implementemos historia):
    public function stories()
    {
        return $this->hasMany(EventStory::class);
    }
}
