<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSchedule extends Model
{
    use HasFactory;

    /**
     * Atributos asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'event_id',
        'title',
        'description',
        'starts_at',
        'ends_at',
        'location_label',
        'location_type',
        'display_order',
    ];

    /**
     * Casts de atributos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'starts_at'     => 'datetime',
        'ends_at'       => 'datetime',
        'display_order' => 'integer',
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

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Ordena el itinerario en orden cronológico.
     */
    public function scopeOrdered($query)
    {
        return $query
            ->orderBy('starts_at')
            ->orderBy('display_order')
            ->orderBy('id');
    }
}
