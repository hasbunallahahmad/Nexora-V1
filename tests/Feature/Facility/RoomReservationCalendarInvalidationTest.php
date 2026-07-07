<?php

use App\Calendar\Events\CalendarSourceChanged;
use App\Shared\Enums\ReservationStatus;
use App\Facility\Models\Room;
use App\Facility\Models\RoomReservation;
use App\Facility\Services\RoomReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

it('triggers CalendarSourceChanged when a reservation is submitted', function () {
    Event::fake([CalendarSourceChanged::class]);

    $room = Room::factory()->create();
    $draft = RoomReservation::factory()->create([
        'room_id' => $room->id,
        'status'  => ReservationStatus::Draft,
    ]);

    app(RoomReservationService::class)->submit($draft->id);

    Event::assertDispatched(CalendarSourceChanged::class, fn($event) => $event->sourceType === 'room_reservation');
});
