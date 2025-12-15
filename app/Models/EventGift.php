<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventGift extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Posibles estados del regalo.
     */
    public const STATUS_PENDING   = 'pending';
    public const STATUS_RESERVED  = 'reserved';
    public const STATUS_PURCHASED = 'purchased';

    /**
     * Atributos asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'event_id',
        'name',
        'description',
        'store_label',
        'url',
        'quantity',
        'quantity_reserved',
        'status',
        'claimed_by_guest_id',
        'reserved_at',
        'purchased_at',
        'display_order',
    ];

    /**
     * Casts de atributos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity'          => 'integer',
        'quantity_reserved' => 'integer',
        'reserved_at'       => 'datetime',
        'purchased_at'      => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function claimedByGuest()
    {
        return $this->belongsTo(Guest::class, 'claimed_by_guest_id');
    }

    public function claims()
    {
        return $this->hasMany(EventGiftClaim::class, 'gift_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForEvent(Builder $query, Event $event): Builder
    {
        return $query->where('event_id', $event->id);
    }

    /**
     * Devuelve solo los regalos que deberían verse en la lista pública,
     * respetando si se deben ocultar los purchased.
     */
    public function scopePublicList(Builder $query, Event $event, bool $hidePurchased = false): Builder
    {
        $query->forEvent($event);

        if ($hidePurchased) {
            $query->where('status', '!=', self::STATUS_PURCHASED);
        }

        return $query->orderBy('display_order')->orderBy('id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function getAvailableUnitsAttribute(): int
    {
        $available = $this->quantity - $this->quantity_reserved;

        return max($available, 0);
    }

    public function isFullyReserved(): bool
    {
        return $this->available_units <= 0;
    }
}
