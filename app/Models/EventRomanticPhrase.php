<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventRomanticPhrase extends Model
{
    use HasFactory;

    protected $table = 'event_romantic_phrases';

    protected $fillable = [
        'event_id',
        'phrase',
        'author',
        'display_order',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled'    => 'boolean',
        'display_order' => 'integer',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }
}
