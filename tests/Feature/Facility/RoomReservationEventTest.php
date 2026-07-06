<?php

use App\Facility\DTO\ApproveReservationData;
use App\Facility\DTO\RejectReservationData;
use App\Facility\Enums\ReservationStatus;
use App\Facility\Events\ReservationApproved;
use App\Facility\Events\ReservationCancelled;
use App\Facility\Events\ReservationRejected;
use App\Facility\Events\ReservationSubmitted;
use App\Facility\Models\Room;
use App\Facility\Models\RoomReservation;
use App\Facility\Services\RoomReservationService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

it('dispatches ReservationSubmitted when a draft is submitted', function () {
    Event::fake([ReservationSubmitted::class]);

    $room = Room::factory()->create();
    $draft = RoomReservation::factory()->create([
        'room_id' => $room->id,
        'status'  => ReservationStatus::Draft,
    ]);

    app(RoomReservationService::class)->submit($draft->id);

    Event::assertDispatched(ReservationSubmitted::class);
});

it('dispatches ReservationApproved when a submitted reservation is approved', function () {
    Event::fake([ReservationApproved::class]);

    $room = Room::factory()->create();
    $approver = User::factory()->create();
    $submitted = RoomReservation::factory()->create([
        'room_id' => $room->id,
        'status'  => ReservationStatus::Submitted,
    ]);

    app(RoomReservationService::class)->approve(new ApproveReservationData($submitted->id, $approver->id));

    Event::assertDispatched(ReservationApproved::class);
});

it('dispatches ReservationRejected when a submitted reservation is rejected', function () {
    Event::fake([ReservationRejected::class]);

    $room = Room::factory()->create();
    $approver = User::factory()->create();
    $submitted = RoomReservation::factory()->create([
        'room_id' => $room->id,
        'status'  => ReservationStatus::Submitted,
    ]);

    app(RoomReservationService::class)->reject(new RejectReservationData($submitted->id, $approver->id, 'Bentrok jadwal lain'));

    Event::assertDispatched(ReservationRejected::class);
});

it('dispatches ReservationCancelled when an approved reservation is cancelled', function () {
    Event::fake([ReservationCancelled::class]);

    $room = Room::factory()->create();
    $approved = RoomReservation::factory()->create([
        'room_id' => $room->id,
        'status'  => ReservationStatus::Approved,
    ]);

    app(RoomReservationService::class)->cancel($approved->id);

    Event::assertDispatched(ReservationCancelled::class);
});

it('does not dispatch ReservationSubmitted when submit fails due to conflict', function () {
    Event::fake([ReservationSubmitted::class]);

    $room = Room::factory()->create();

    RoomReservation::factory()->create([
        'room_id'        => $room->id,
        'status'         => ReservationStatus::Submitted,
        'start_datetime' => now()->addHours(2),
        'end_datetime'   => now()->addHours(4),
    ]);

    $draft = RoomReservation::factory()->create([
        'room_id'        => $room->id,
        'status'         => ReservationStatus::Draft,
        'start_datetime' => now()->addHours(3),
        'end_datetime'   => now()->addHours(5),
    ]);

    try {
        app(RoomReservationService::class)->submit($draft->id);
    } catch (\App\Facility\Exceptions\ReservationConflictException) {
        // expected
    }

    Event::assertNotDispatched(ReservationSubmitted::class);
});
