<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventDressCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'title',
        'description',
        'examples',
        'notes',
        'icon',
        'example_photo_id',
        'display_order',
        'is_enabled',
    ];

    protected $casts = [
        'example_photo_id' => 'integer',
        'display_order'    => 'integer',
        'is_enabled'       => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function examplePhoto()
    {
        return $this->belongsTo(EventPhoto::class, 'example_photo_id');
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }
}
