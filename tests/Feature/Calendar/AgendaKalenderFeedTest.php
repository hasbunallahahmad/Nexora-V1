<?php

use App\Activity\Models\Agenda;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('only returns published agenda on the public calendar feed', function () {
    $published = Agenda::factory()->create([
        'is_published' => true,
        'start_date'   => now()->addDay(),
    ]);

    Agenda::factory()->create([
        'is_published' => false,
        'start_date'   => now()->addDay(),
    ]);

    $response = $this->getJson('/api/agenda/kalender?' . http_build_query([
        'start' => now()->toDateString(),
        'end'   => now()->addWeek()->toDateString(),
    ]));

    $response->assertOk();
    $ids = collect($response->json())->pluck('id');

    expect($ids)->toContain($published->id)
        ->and($ids)->toHaveCount(1);
});
