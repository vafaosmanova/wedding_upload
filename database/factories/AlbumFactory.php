<?php

namespace Database\Factories;

use App\Models\Album;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class AlbumFactory extends Factory
{
    protected $model = Album::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(2),
            'user_id' => 1,
            'url' => 'album/' . uniqid(),
            'pin' => Hash::make('1234'),
            'qr_code' => null,
        ];
    }
}
