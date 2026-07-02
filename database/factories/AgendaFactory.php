<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Agenda;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Agenda>
 */
class AgendaFactory extends Factory
{
    protected $model = Agenda::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('now', '+1 month');

        return [
            'judul_agenda' => fake()->sentence(4),
            'deskripsi'    => fake()->optional()->sentence(10),
            'location'     => fake()->city(),
            'start_date'   => $start,
            'end_date'     => null,
            'is_published' => true,
        ];
    }
}
