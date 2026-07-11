<?php

use App\Activity\Models\Agenda as ActivityAgenda;
use App\Models\Agenda as LegacyAgenda;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('legacy Agenda class is an instance of the new Activity Agenda class', function () {
    $agenda = LegacyAgenda::create([
        'judul_agenda' => 'Test Instance Legacy',
        'location'     => 'Aula A',
        'start_date'   => now()->addDay(),
        'is_published' => true,
    ]);

    expect($agenda)->toBeInstanceOf(ActivityAgenda::class);
});

it('generates slug identically whether created via legacy or new namespace', function () {
    $legacy = LegacyAgenda::create([
        'judul_agenda' => 'Rapat Koordinasi Legacy',
        'location'     => 'Aula A',
        'start_date'   => now()->addDay(),
        'is_published' => true,
    ]);

    $baru = ActivityAgenda::create([
        'judul_agenda' => 'Rapat Koordinasi Baru',
        'location'     => 'Aula B',
        'start_date'   => now()->addDay(),
        'is_published' => true,
    ]);

    expect($legacy->slug)->toBe('rapat-koordinasi-legacy');
    expect($baru->slug)->toBe('rapat-koordinasi-baru');
});

it('scopePublished works identically via both namespaces', function () {
    LegacyAgenda::create([
        'judul_agenda' => 'Agenda Terbit',
        'location'     => 'Aula A',
        'start_date'   => now()->addDay(),
        'is_published' => true,
    ]);

    LegacyAgenda::create([
        'judul_agenda' => 'Agenda Draft',
        'location'     => 'Aula A',
        'start_date'   => now()->addDay(),
        'is_published' => false,
    ]);

    $legacyCount = LegacyAgenda::published()->count();
    $activityCount = ActivityAgenda::published()->count();

    expect($legacyCount)->toBe(1);
    expect($activityCount)->toBe(1);
});

it('preserves the legacy activity log subject class name when created via the legacy namespace', function () {
    // Sengaja pakai Model::create() langsung (bukan factory), karena ini
    // yang merepresentasikan pemakaian produksi sesungguhnya —
    // Model::create() menghormati late static binding, sedangkan
    // Factory::create() tidak (Factory selalu memakai class di property
    // $model, terlepas dari class yang memanggil ::factory()).
    $agenda = LegacyAgenda::create([
        'judul_agenda' => 'Test Subject Type',
        'location'     => 'Aula A',
        'start_date'   => now()->addDay(),
        'is_published' => true,
    ]);

    $log = \Spatie\Activitylog\Models\Activity::query()->latest('id')->first();

    expect($log)->not->toBeNull();
    expect($log->subject_type)->toBe(LegacyAgenda::class);
});

it('exposes isVisibleToPublic as an expressive alias for is_published', function () {
    $published = ActivityAgenda::create([
        'judul_agenda' => 'Agenda Publik',
        'location'     => 'Aula A',
        'start_date'   => now()->addDay(),
        'is_published' => true,
    ]);

    $unpublished = ActivityAgenda::create([
        'judul_agenda' => 'Agenda Privat',
        'location'     => 'Aula A',
        'start_date'   => now()->addDay(),
        'is_published' => false,
    ]);

    expect($published->isVisibleToPublic())->toBeTrue();
    expect($unpublished->isVisibleToPublic())->toBeFalse();
});
