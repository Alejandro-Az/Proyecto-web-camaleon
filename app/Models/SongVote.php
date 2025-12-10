<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SongVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'song_id',
        'guest_id',
        'fingerprint',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function song()
    {
        return $this->belongsTo(EventSong::class, 'song_id');
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }
}
