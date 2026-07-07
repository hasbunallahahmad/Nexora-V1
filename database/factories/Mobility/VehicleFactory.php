<?php

declare(strict_types=1);

namespace Database\Factories\Mobility;

use App\Mobility\Enums\VehicleStatus;
use App\Mobility\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        return [
            'name'         => fake()->unique()->words(2, true) . ' ' . fake()->randomElement(['Innova', 'Avanza', 'Hilux', 'Elf']),
            'plate_number' => 'H ' . fake()->unique()->numerify('####') . ' ' . fake()->randomLetter() . fake()->randomLetter(),
            'type'         => fake()->randomElement(['MPV', 'Bus', 'Pickup', 'Sedan']),
            'capacity'     => fake()->numberBetween(4, 40),
            'driver_name'  => fake()->name(),
            'status'       => VehicleStatus::Active,
        ];
    }
}
