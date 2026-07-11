<?php

use App\Calendar\Events\CalendarSourceChanged;
use App\Models\Agenda;
use App\Activity\Models\Agenda as ActivityAgenda;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

it('dispatches CalendarSourceChanged when an agenda is created via the new Activity namespace', function () {
    Event::fake([CalendarSourceChanged::class]);

    ActivityAgenda::create([
        'judul_agenda' => 'Test Observer Activity Namespace',
        'location'     => 'Aula A',
        'start_date'   => now()->addDay(),
        'is_published' => true,
    ]);

    Event::assertDispatchedTimes(CalendarSourceChanged::class, 1);
});

it('dispatches CalendarSourceChanged exactly once when an agenda is updated', function () {
    $agenda = Agenda::factory()->create();

    Event::fake([CalendarSourceChanged::class]);

    $agenda->update(['judul_agenda' => 'Judul Baru Yang Valid']);

    Event::assertDispatchedTimes(CalendarSourceChanged::class, 1);
});
