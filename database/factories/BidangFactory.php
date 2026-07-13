<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Bidang;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bidang>
 */
class BidangFactory extends Factory
{
    protected $model = Bidang::class;

    public function definition(): array
    {
        return [
            'nama_bidang' => fake()->unique()->words(2, true) . ' Bidang',
        ];
    }
}
