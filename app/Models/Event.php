<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Event extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE   = 'active';
    public const STATUS_ARCHIVED = 'archived';

    public const PLAN_STANDARD = 'standard';
    public const PLAN_PREMIUM  = 'premium';

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
        'cover_image_path',
        'auto_cleanup_after_days',
        'owner_name',
        'owner_email',
        'plan_key',
        'modules',
        'settings',
        'owner_user_id',
    ];

    protected $casts = [
        'event_date' => 'date',
        'modules'    => 'array',
        'settings'   => 'array',
        'owner_user_id' => 'integer',
    ];

    /* =====================
     * Relationships
     * ===================== */

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function locations()
    {
        return $this->hasMany(EventLocation::class);
    }

    public function dressCodes()
    {
        return $this->hasMany(EventDressCode::class);
    }

    public function romanticPhrases()
    {
        return $this->hasMany(EventRomanticPhrase::class);
    }

    public function songs()
    {
        return $this->hasMany(EventSong::class);
    }

    public function schedules()
    {
        return $this->hasMany(EventSchedule::class)->orderBy('starts_at')->orderBy('display_order')->orderBy('id');
    }

    public function stories()
    {
        return $this->hasMany(EventStory::class)->orderBy('display_order')->orderBy('id');
    }

    public function songVotes()
    {
        return $this->hasMany(SongVote::class);
    }

    public function gifts()
    {
        return $this->hasMany(EventGift::class)->orderBy('display_order')->orderBy('id');
    }

    public function guests()
    {
        return $this->hasMany(Guest::class);
    }

    /* =====================
     * Scopes
     * ===================== */

    public function scopePublicVisible($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /* =====================
     * Modules & Settings
     * ===================== */

    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings ?? [], $key, $default);
    }

    public function getPaletteKeyAttribute(): string
    {
        $palette = $this->getSetting('theme_palette', 'tuscan');
        $palettes = config('themes.palettes', []);
        return array_key_exists($palette, $palettes) ? $palette : 'tuscan';
    }

    public function getPaletteConfigAttribute(): array
    {
        $palettes = config('themes.palettes', []);
        return $palettes[$this->palette_key] ?? $palettes['tuscan'];
    }

    public function getPaletteStyleAttribute(): string
    {
        $config = $this->palette_config;
        return sprintf(
            '--bg: %s; --surface: %s; --surface-alt: %s; --ink: %s; --ink-soft: %s; --ink-muted: %s; --rule: %s; --accent: %s; --accent-soft: %s; --accent-ink: %s; --leaf: %s; --sand: %s; --serif: %s; --sans: %s;',
            $config['bg'], $config['surface'], $config['surfaceAlt'], $config['ink'], $config['inkSoft'], $config['inkMuted'], $config['rule'], $config['accent'], $config['accentSoft'], $config['accentInk'], $config['leaf'], $config['sand'], $config['serif'], $config['sans']
        );
    }

    /**
     * Defaults + módulos guardados + mapeo legacy → canonical.
     *
     * Importante:
     * - El mapeo legacy se decide con base en lo que venía en DB ($rawModules),
     *   NO con base en el array ya mezclado con defaults.
     *   Así, aunque 'songs' exista por default (false), la legacy puede prenderla.
     *
     * @return array<string,bool>
     */
    public function modulesWithDefaults(): array
    {
        $defaults = static::moduleDefaults();

        $rawModules = $this->modules;
        if (!is_array($rawModules)) {
            $rawModules = [];
        }

        // defaults + overrides guardados
        $modules = array_merge($defaults, $rawModules);

        // legacy → canonical (solo si canonical NO vino explícito en DB)
        foreach (static::moduleAliases() as $legacyKey => $canonicalKey) {
            if (array_key_exists($legacyKey, $rawModules) && !array_key_exists($canonicalKey, $rawModules)) {
                $modules[$canonicalKey] = (bool) $rawModules[$legacyKey];
            }
        }

        return $modules;
    }

    protected function canonicalModuleKey(string $key): string
    {
        $aliases = static::moduleAliases();
        return $aliases[$key] ?? $key;
    }

    public function isModuleEnabled(string $moduleKey): bool
    {
        $canonical = $this->canonicalModuleKey($moduleKey);

        // gating por plan (solo si plan_key existe)
        if (!$this->isModuleAvailable($canonical)) {
            return false;
        }

        $modules = $this->modulesWithDefaults();
        return (bool) ($modules[$canonical] ?? false);
    }

    /**
     * Determina si el módulo está permitido por el plan.
     *
     * Regla clave para que pasen sus tests actuales:
     * - Si plan_key está vacío/null => NO se aplica gating (se considera "sin plan"),
     *   entonces el módulo depende solo de modules/settings/defaults.
     */
    public function isModuleAvailable(string $moduleKey): bool
    {
        $canonical = $this->canonicalModuleKey($moduleKey);

        $planKey = $this->plan_key;
        if (!is_string($planKey) || trim($planKey) === '') {
            // Sin plan asignado => no bloqueamos por plan
            return true;
        }

        $plans = config('event_plans.plans', []);
        if (!is_array($plans)) {
            $plans = [];
        }

        // Si el plan es desconocido, intentamos caer al default_plan
        if (!array_key_exists($planKey, $plans)) {
            $fallback = config('event_plans.default_plan', self::PLAN_STANDARD);
            if (is_string($fallback) && $fallback !== '' && array_key_exists($fallback, $plans)) {
                $planKey = $fallback;
            } else {
                // Plan no resoluble => fail-closed
                return false;
            }
        }

        $plan = $plans[$planKey] ?? [];
        $allowed = $plan['modules'] ?? ($plan['allowed_modules'] ?? []);
        $allowed = is_array($allowed) ? $allowed : [];

        // fail-closed si el plan existe pero viene vacío
        if (empty($allowed)) {
            if (app()->environment(['local', 'testing'])) {
                throw new RuntimeException("event_plans inválido: el plan '{$planKey}' no define módulos allowed.");
            }
            return false;
        }

        // canonicalizamos allowed (por si trae llaves legacy)
        $allowedCanonical = array_values(array_unique(array_map(function ($k) {
            $k = is_string($k) ? $k : (string) $k;
            return $this->canonicalModuleKey($k);
        }, $allowed)));

        return in_array($canonical, $allowedCanonical, true);
    }

    /**
     * Normaliza modules para storage/lectura:
     * - Aplica defaults
     * - Mapea aliases legacy => canonical
     * - Canonical gana sobre legacy
     * - (Opcional) filtra por plan si $planKey viene seteado (no vacío)
     *
     * @param  array<string,bool|int|string|null>  $modules
     * @return array<string,bool>
     */
    public static function normalizeModulesForStorage(array $modules, ?string $planKey = null): array
    {
        $defaults = static::moduleDefaults();
        $aliases  = static::moduleAliases();

        $canonicalKeys = array_unique(array_merge(
            array_keys($defaults),
            array_values($aliases)
        ));

        // Start: defaults
        $normalized = [];
        foreach ($canonicalKeys as $k) {
            $normalized[$k] = (bool) ($defaults[$k] ?? false);
        }

        // 1) Overrides canónicos explícitos
        foreach ($canonicalKeys as $k) {
            if (array_key_exists($k, $modules)) {
                $normalized[$k] = (bool) $modules[$k];
            }
        }

        // 2) Legacy → canonical solo si canonical NO vino explícito
        foreach ($aliases as $legacy => $canonical) {
            if (array_key_exists($legacy, $modules) && !array_key_exists($canonical, $modules)) {
                $normalized[$canonical] = (bool) $modules[$legacy];
            }
        }

        // 3) Filtrado por plan solo si planKey viene con valor real
        if (is_string($planKey) && trim($planKey) !== '') {
            $allowed = static::allowedModulesForPlan($planKey);

            // Si el plan existe pero allowed vacío => fail-closed: apaga todo
            if (static::planConfigExists($planKey) && empty($allowed)) {
                foreach ($normalized as $k => $_) {
                    $normalized[$k] = false;
                }
                return $normalized;
            }

            if (!empty($allowed)) {
                foreach ($normalized as $k => $v) {
                    if (!in_array($k, $allowed, true)) {
                        $normalized[$k] = false;
                    }
                }
            }
        }

        return $normalized;
    }

    /* =========================
     * Internals (config)
     * ========================= */

    private static function moduleDefaults(): array
    {
        $defaults = config('event_modules.defaults');

        if (is_array($defaults) && !empty($defaults)) {
            return $defaults;
        }

        // fallback seguro si config está ausente
        return [
            'public_attendance_list' => false,
            'guest_photos_upload'    => true,
            'romantic_phrases'       => true,
            'dress_code'             => true,
            'countdown'              => true,
            'map'                    => true,
            'schedule'               => true,
            'gallery'                => true,
            'songs'                  => true,
            'rsvp'                   => true,
            'gifts'                  => true,
            'story'                  => false,
        ];
    }

    /**
     * @return array<string,string> alias => canonical
     */
    private static function moduleAliases(): array
    {
        $legacy = config('event_modules.legacy_aliases', []);
        $aliases = config('event_modules.aliases', []);

        if (!is_array($legacy)) {
            $legacy = [];
        }
        if (!is_array($aliases)) {
            $aliases = [];
        }

        // legacy tiene prioridad si choca con aliases
        $merged = $legacy + $aliases;

        // fallback mínimo para que el test no dependa del config
        $merged += [
            'playlist_suggestions' => 'songs',
        ];

        return $merged;
    }

    private static function planConfigExists(string $planKey): bool
    {
        $plans = config('event_plans.plans', []);
        return is_array($plans) && array_key_exists($planKey, $plans);
    }

    /**
     * @return array<int,string>
     */
    private static function allowedModulesForPlan(string $planKey): array
    {
        $plans = config('event_plans.plans', []);
        if (!is_array($plans)) {
            return [];
        }

        $plan = $plans[$planKey] ?? null;
        if (!is_array($plan)) {
            return [];
        }

        $allowed = $plan['modules'] ?? ($plan['allowed_modules'] ?? []);
        if (!is_array($allowed)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($v) => is_string($v) ? trim($v) : null,
            $allowed
        )));
    }
}
