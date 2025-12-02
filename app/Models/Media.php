<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static where(string $string, $album_id)
 * @method static find($id)
 * @method static findOrFail(string $media_id)
 */
class Media extends Model
{
    use HasFactory;
    protected $fillable = [
        'album_id',
        'filename',
        'path',
        'mime_type',
        'approved',
    ];
    protected $casts = [
        'approved' => 'boolean',
    ];

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }
}
