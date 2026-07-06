<?php

declare(strict_types=1);

namespace Database\Factories\Facility;

use App\Facility\Enums\RoomStatus;
use App\Facility\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{

    protected $model = Room::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true) . 'Room',
            'location' => fake()->buildingNumber() . 'Floor',
            'capacity' => fake()->numberBetween(4, 50),
            'status' => RoomStatus::Active,
        ];
    }
}
