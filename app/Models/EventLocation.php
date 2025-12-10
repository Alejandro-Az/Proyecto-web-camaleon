<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'type',
        'name',
        'address',
        'maps_url',
        'display_order',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
