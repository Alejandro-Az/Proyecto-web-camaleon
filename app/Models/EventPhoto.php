<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventPhoto extends Model
{
    use HasFactory;
    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Constantes de tipo de foto
    |--------------------------------------------------------------------------
    */

    public const TYPE_GALLERY      = 'gallery';
    public const TYPE_HERO         = 'hero';
    public const TYPE_GUEST_UPLOAD = 'guest_upload';
    public const TYPE_DRESS_CODE   = 'dress_code';

    // ✅ NUEVO: imágenes para el módulo Historia
    public const TYPE_STORY        = 'story';

    /*
    |--------------------------------------------------------------------------
    | Constantes de estado
    |--------------------------------------------------------------------------
    */

    public const STATUS_APPROVED = 'approved';
    public const STATUS_PENDING  = 'pending';
    public const STATUS_REJECTED = 'rejected';

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

    protected $casts = [
        'display_order' => 'integer',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
