<?php

use App\Calendar\Events\CalendarSourceChanged;
use App\Models\Agenda;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

it('dispatches CalendarSourceChanged exactly once when an agenda is created', function () {
    Event::fake([CalendarSourceChanged::class]);

    Agenda::factory()->create();

    Event::assertDispatchedTimes(CalendarSourceChanged::class, 1);
});

it('dispatches CalendarSourceChanged exactly once when an agenda is updated', function () {
    $agenda = Agenda::factory()->create();

    Event::fake([CalendarSourceChanged::class]);

    $agenda->update(['judul_agenda' => 'Judul Baru Yang Valid']);

    Event::assertDispatchedTimes(CalendarSourceChanged::class, 1);
});
