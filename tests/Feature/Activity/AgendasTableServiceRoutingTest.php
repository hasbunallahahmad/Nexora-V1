<?php

use App\Activity\Models\Agenda;
use App\Activity\Services\AgendaService;
use App\Models\Bidang;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates an agenda through AgendaService with bidang synced', function () {
    $bidang = Bidang::factory()->create();

    $agenda = app(AgendaService::class)->create([
        'judul_agenda' => 'Rapat Lewat Table Action',
        'location'     => 'Aula Utama',
        'start_date'   => now()->addDay(),
        'is_published' => true,
        'bidang_id'    => [$bidang->id],
    ]);

    expect($agenda)->toBeInstanceOf(Agenda::class)
        ->and($agenda->bidang()->count())->toBe(1);
});
