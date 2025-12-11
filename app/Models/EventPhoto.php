<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventPhoto extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const TYPE_GALLERY      = 'gallery';
    public const TYPE_HERO         = 'hero';
    public const TYPE_GUEST_UPLOAD = 'guest_upload';

    public const STATUS_APPROVED = 'approved';
    public const STATUS_PENDING  = 'pending';
    public const STATUS_REJECTED = 'rejected';

    /**
     * Atributos asignables en masa.
     *
     * Permite usar EventPhoto::create([...]).
     */
    protected $fillable = [
        'event_id',
        'guest_id',
        'type',
        'file_path',
        'thumbnail_path',
        'caption',
        'status',
        'display_order',
    ];

    /**
     * Evento al que pertenece esta foto.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Invitado que subió la foto (null en galería oficial).
     */
    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    /**
     * Scope: solo fotos aprobadas.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope: filtrar por tipo (gallery, hero, guest_upload, etc.).
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
