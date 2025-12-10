<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSong extends Model
{
    use HasFactory;

    /**
     * Posibles estados de la sugerencia de canción.
     */
    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /**
     * Atributos asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'event_id',
        'title',
        'artist',
        'url',
        'message_for_couple',
        'suggested_by_guest_id',
        'show_author',
        'status',
        'votes_count',
    ];

    /**
     * Casts.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'show_author' => 'boolean',
    ];

    /**
     * Scope para obtener sólo canciones aprobadas.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function suggestedBy()
    {
        return $this->belongsTo(Guest::class, 'suggested_by_guest_id');
    }

    public function votes()
    {
        return $this->hasMany(SongVote::class, 'song_id');
    }
}
