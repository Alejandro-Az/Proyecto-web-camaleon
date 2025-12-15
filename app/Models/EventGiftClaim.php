<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventGiftClaim extends Model
{
    use HasFactory;

    public const STATUS_RESERVED  = 'reserved';
    public const STATUS_PURCHASED = 'purchased';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Atributos asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'event_id',
        'gift_id',
        'guest_id',
        'quantity',
        'status',
    ];

    protected $casts = [
        'quantity' => 'integer',
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

    public function gift()
    {
        return $this->belongsTo(EventGift::class, 'gift_id');
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }
}
