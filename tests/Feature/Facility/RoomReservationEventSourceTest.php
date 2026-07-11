<?php

use App\Calendar\DTO\CalendarQuery;
use App\Calendar\Enums\CalendarAudience;
use App\Shared\Enums\ReservationStatus;
use App\Facility\Models\Room;
use App\Facility\Models\RoomReservation;
use App\Facility\Services\Sources\RoomReservationEventSource;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('excludes submitted reservations from the public audience', function () {
    $room = Room::factory()->create();

    $approved = RoomReservation::factory()->create([
        'room_id'        => $room->id,
        'status'         => ReservationStatus::Approved,
        'start_datetime' => now()->addDay(),
        'end_datetime'   => now()->addDay()->addHours(2),
    ]);

    RoomReservation::factory()->create([
        'room_id'        => $room->id,
        'status'         => ReservationStatus::Submitted,
        'start_datetime' => now()->addDays(2),
        'end_datetime'   => now()->addDays(2)->addHours(2),
    ]);

    $query = new CalendarQuery(now(), now()->addWeek(), CalendarAudience::Public);
    $events = (new RoomReservationEventSource())->events($query);

    expect($events)->toHaveCount(1)
        ->and($events->first()->id)->toBe($approved->id);
});

it('includes submitted reservations for the admin audience', function () {
    $room = Room::factory()->create();

    RoomReservation::factory()->create([
        'room_id'        => $room->id,
        'status'         => ReservationStatus::Submitted,
        'start_datetime' => now()->addDay(),
        'end_datetime'   => now()->addDay()->addHours(2),
    ]);

    $query = new CalendarQuery(now(), now()->addWeek(), CalendarAudience::Admin);
    $events = (new RoomReservationEventSource())->events($query);

    expect($events)->toHaveCount(1);
});

it('excludes draft and rejected reservations for any audience', function () {
    $room = Room::factory()->create();

    RoomReservation::factory()->create([
        'room_id'        => $room->id,
        'status'         => ReservationStatus::Draft,
        'start_datetime' => now()->addDay(),
        'end_datetime'   => now()->addDay()->addHours(2),
    ]);

    RoomReservation::factory()->create([
        'room_id'        => $room->id,
        'status'         => ReservationStatus::Rejected,
        'start_datetime' => now()->addDays(2),
        'end_datetime'   => now()->addDays(2)->addHours(2),
    ]);

    $query = new CalendarQuery(now(), now()->addWeek(), CalendarAudience::Admin);
    $events = (new RoomReservationEventSource())->events($query);

    expect($events)->toHaveCount(0);
});

it('shows guest name and instansi when reservation has no linked user', function () {
    $room = Room::factory()->create();

    RoomReservation::factory()->create([
        'room_id'        => $room->id,
        'status'         => ReservationStatus::Approved,
        'requested_by'   => null,
        'guest_name'     => 'Gogon',
        'guest_instansi' => 'KONI',
        'start_datetime' => now()->addDay(),
        'end_datetime'   => now()->addDay()->addHours(2),
    ]);

    $query = new CalendarQuery(now(), now()->addWeek(), CalendarAudience::Public);
    $events = (new RoomReservationEventSource())->events($query);

    expect($events->first()->extendedProps['requestedBy'])->toBe('Gogon (KONI)');
});

it('shows requested user name when reservation is linked to an account', function () {
    $room = Room::factory()->create();
    $user = \App\Models\User::factory()->create(['name' => 'Budi Santoso']);

    RoomReservation::factory()->create([
        'room_id'        => $room->id,
        'status'         => ReservationStatus::Approved,
        'requested_by'   => $user->id,
        'start_datetime' => now()->addDay(),
        'end_datetime'   => now()->addDay()->addHours(2),
    ]);

    $query = new CalendarQuery(now(), now()->addWeek(), CalendarAudience::Public);
    $events = (new RoomReservationEventSource())->events($query);

    expect($events->first()->extendedProps['requestedBy'])->toBe('Budi Santoso');
});
