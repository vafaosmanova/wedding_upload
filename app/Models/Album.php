<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use SimpleSoftwareIO\QrCode\Generator;

/**
 * @method static create(array $array)
 * @method static where(string $string, int|string|null $id)
 * @method static findOrFail($id)
 * @method static find($album_id)
 * @property mixed $id
 * @property mixed $qr_code
 * @property mixed $title
 */
class Album extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'user_id',
        'qr_code',
    ];

    public static function createWithQr(array $attributes): self
    {
        $album = self::create($attributes);
        $album->qr_code = app(Generator::class)
            ->size(200)
            ->generate(url('/gast/' . $album->id));
        $album->save();
        return $album;
    }
    //Beziehung: Ein Album gehört zu einem User (n:1)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    //Beziehung: Ein Album hat viele Medien (1:n)
    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }
    //Beziehung: Ein Album hat einen Pin (1:1)
    public function pin(): HasOne
    {
        return $this->hasOne(Pin::class);
    }
}
