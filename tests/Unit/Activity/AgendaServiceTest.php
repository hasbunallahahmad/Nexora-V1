<?php

use App\Activity\Models\Agenda;
use App\Activity\Repositories\AgendaRepository;
use App\Activity\Services\AgendaService;
use App\Models\Bidang;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('creates an agenda and syncs bidang relations', function () {
    $bidangA = Bidang::factory()->create();
    $bidangB = Bidang::factory()->create();

    $service = app(AgendaService::class);

    $agenda = $service->create([
        'judul_agenda' => 'Rapat Koordinasi Lintas Bidang',
        'location'     => 'Aula Utama',
        'start_date'   => now()->addDay(),
        'is_published' => true,
        'bidang_id'    => [$bidangA->id, $bidangB->id],
    ]);

    expect($agenda)->toBeInstanceOf(Agenda::class)
        ->and($agenda->slug)->toBe('rapat-koordinasi-lintas-bidang')
        ->and($agenda->bidang()->count())->toBe(2);
});

it('throws validation exception when judul_agenda is too short', function () {
    $service = app(AgendaService::class);

    $service->create([
        'judul_agenda' => 'Rpt',
        'location'     => 'Aula Utama',
        'start_date'   => now()->addDay(),
    ]);
})->throws(ValidationException::class);

it('updates an agenda and re-syncs bidang relations', function () {
    $agenda = Agenda::create([
        'judul_agenda' => 'Agenda Awal',
        'location'     => 'Aula A',
        'start_date'   => now()->addDay(),
        'is_published' => false,
    ]);

    $bidangBaru = Bidang::factory()->create();

    $service = app(AgendaService::class);
    $updated = $service->update($agenda->id, [
        'judul_agenda' => 'Agenda Sudah Direvisi',
        'location'     => 'Aula A',
        'start_date'   => now()->addDay(),
        'bidang_id'    => [$bidangBaru->id],
    ]);

    expect($updated->slug)->toBe('agenda-sudah-direvisi')
        ->and($updated->bidang()->count())->toBe(1)
        ->and($updated->bidang()->first()->id)->toBe($bidangBaru->id);
});

it('deletes an agenda via the service', function () {
    $agenda = Agenda::create([
        'judul_agenda' => 'Agenda Akan Dihapus',
        'location'     => 'Aula A',
        'start_date'   => now()->addDay(),
    ]);

    $service = app(AgendaService::class);

    expect($service->delete($agenda->id))->toBeTrue();
    expect(Agenda::find($agenda->id))->toBeNull();
});

it('finds an agenda by slug via the repository', function () {
    Agenda::create([
        'judul_agenda' => 'Agenda Dicari Lewat Slug',
        'location'     => 'Aula A',
        'start_date'   => now()->addDay(),
    ]);

    $service = app(AgendaService::class);
    $found = $service->findBySlug('agenda-dicari-lewat-slug');

    expect($found)->not->toBeNull()
        ->and($found->judul_agenda)->toBe('Agenda Dicari Lewat Slug');
});
