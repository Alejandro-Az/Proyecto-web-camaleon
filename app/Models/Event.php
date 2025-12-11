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

    /**
     * Posibles valores de status de un evento.
     */
    public const STATUS_DRAFT    = 'draft';
    public const STATUS_ACTIVE   = 'active';
    public const STATUS_FINISHED = 'finished';
    public const STATUS_ARCHIVED = 'archived';
    public const STATUS_DELETED  = 'deleted';

    /**
     * Atributos asignables en masa.
     *
     * @var array<int, string>
     */
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

    /**
     * Casts de atributos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'event_date' => 'date',
        'modules'    => 'array',
        'settings'   => 'array',
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
}
