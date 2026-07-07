<?php

declare(strict_types=1);

namespace Database\Factories\Mobility;

use App\Mobility\Models\Vehicle;
use App\Mobility\Models\VehicleReservation;
use App\Models\User;
use App\Shared\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VehicleReservation>
 */
class VehicleReservationFactory extends Factory
{
    protected $model = VehicleReservation::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+1 day', '+1 week');

        return [
            'vehicle_id'     => Vehicle::factory(),
            'requested_by'   => User::factory(),
            'title'          => fake()->sentence(4),
            'destination'    => fake()->city(),
            'purpose'        => fake()->optional()->sentence(8),
            'start_datetime' => $start,
            'end_datetime'   => (clone $start)->modify('+2 hours'),
            'status'         => ReservationStatus::Draft,
        ];
    }
}
