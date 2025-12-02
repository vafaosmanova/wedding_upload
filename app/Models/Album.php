<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
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

    public function pin(): HasOne
    {
        return $this->hasOne(Pin::class);
    }
    public static function createWithQr(array $attributes): self
    {
        $album = self::create($attributes);
        $album->qr_code = app(Generator::class)
            ->size(200)
            ->generate(url('/guest/' . $album->id));
        $album->save();
        return $album;
    }
    public function getFileUrl($path): string
    {
        return Storage::disk('sftp')->url($path);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }
    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }
    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }
    public function sharedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'album_user', 'album_id', 'user_id');
    }
}
