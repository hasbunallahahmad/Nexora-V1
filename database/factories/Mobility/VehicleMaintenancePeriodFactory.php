<?php

declare(strict_types=1);

namespace Database\Factories\Mobility;

use App\Mobility\Models\Vehicle;
use App\Mobility\Models\VehicleMaintenancePeriod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VehicleMaintenancePeriod>
 */
class VehicleMaintenancePeriodFactory extends Factory
{
    protected $model = VehicleMaintenancePeriod::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+1 day', '+1 week');

        return [
            'vehicle_id'     => Vehicle::factory(),
            'start_datetime' => $start,
            'end_datetime'   => (clone $start)->modify('+1 day'),
            'reason'         => fake()->randomElement(['Ganti oli', 'Servis KIR', 'Perbaikan rem']),
            'created_by'     => User::factory(),
        ];
    }
}
