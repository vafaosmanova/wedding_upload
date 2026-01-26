<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static where(string $string, $albumId)
 * @method static findOrFail($mediaId)
 * @method static whereHas(string $string, \Closure $param)
 * @method static find(mixed $id)
 * @method static pluck(string $string)
 */
class Photo extends Model
{
    use HasFactory;

    protected $fillable = [
        'album_id',
        'filename',
        'path',
        'approved',
    ];
    protected $appends = ['url'];

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }
}

