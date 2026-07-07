<?php

declare(strict_types=1);

namespace Database\Factories\Facility;

use App\Shared\Enums\ReservationStatus;
use App\Facility\Models\Room;
use App\Facility\Models\RoomReservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomReservation>
 */
class RoomReservationFactory extends Factory
{
    protected $model = RoomReservation::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+1 day', '+1 week');

        return [
            'room_id'        => Room::factory(),
            'requested_by'   => User::factory(),
            'title'          => fake()->sentence(4),
            'purpose'        => fake()->optional()->sentence(8),
            'start_datetime' => $start,
            'end_datetime'   => (clone $start)->modify('+2 hours'),
            'status'         => ReservationStatus::Draft,
        ];
    }
}
