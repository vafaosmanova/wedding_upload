<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static create(array $array)
 * @method static where(string $string, mixed $input)
 */
class Pin extends Model
{
    protected $fillable = [
        'album_id',
        'pin'
    ];

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }
}
