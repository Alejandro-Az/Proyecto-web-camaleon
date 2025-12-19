<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guest extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Posibles valores de rsvp_status.
     */
    public const RSVP_PENDING = 'pending';
    public const RSVP_YES     = 'yes';
    public const RSVP_NO      = 'no';
    public const RSVP_MAYBE   = 'maybe';

    /**
     * Atributos asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'event_id',
        'name',
        'email',
        'phone',
        'invitation_code',
        'invited_seats',
        'rsvp_status',
        'rsvp_message',
        'rsvp_public',
        'guests_confirmed',
        'show_in_public_list',
        'checked_in_at',

        'seat_label',
        'dietary_tags',
        'dietary_notes',
    ];

    /**
     * Casts.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rsvp_public'         => 'boolean',
        'show_in_public_list' => 'boolean',
        'checked_in_at'       => 'datetime',
        'dietary_tags'        => 'array',
    ];

    /**
     * Scope para invitados que confirmaron asistencia (RSVP YES).
     */
    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('rsvp_status', self::RSVP_YES);
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
}
