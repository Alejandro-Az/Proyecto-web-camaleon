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

    public const TYPE_GALLERY      = 'gallery';      // fotos de galería
    public const TYPE_HERO         = 'hero';         // foto de portada / banner
    public const TYPE_GUEST_UPLOAD = 'guest_upload'; // fotos subidas por invitados

    /*
    |--------------------------------------------------------------------------
    | Constantes de estado
    |--------------------------------------------------------------------------
    */

    public const STATUS_APPROVED = 'approved';
    public const STATUS_PENDING  = 'pending';
    public const STATUS_REJECTED = 'rejected';

    /**
     * Atributos asignables en masa.
     *
     * @var array<int, string>
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
     * Casts de atributos.
     *
     * @var array<string, string>
     */
    protected $casts = [
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

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
