<?php

use App\Shared\Enums\ReservationStatus;
use App\Facility\Repositories\RoomReservationRepository;
use App\Facility\Models\Room;
use App\Facility\Models\RoomReservation;
use App\Shared\DTO\DateRange;
use App\Shared\Services\ConflictDetectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('detects overlapping submitted reservations on the same room', function () {
    $room = Room::factory()->create();

    RoomReservation::factory()->create([
        'room_id'        => $room->id,
        'status'         => ReservationStatus::Submitted,
        'start_datetime' => now()->addHours(2),
        'end_datetime'   => now()->addHours(4),
    ]);

    $service = app(ConflictDetectionService::class);
    $repository = app(RoomReservationRepository::class);

    $overlapping = new DateRange(now()->addHours(3), now()->addHours(5));

    expect($service->hasConflict($repository->occupyingQueryForRoom($room->id), $overlapping))->toBeTrue();
});

it('does not flag adjacent non-overlapping ranges as conflict', function () {
    $room = Room::factory()->create();

    RoomReservation::factory()->create([
        'room_id'        => $room->id,
        'status'         => ReservationStatus::Approved,
        'start_datetime' => now()->addHours(2),
        'end_datetime'   => now()->addHours(4),
    ]);

    $service = app(ConflictDetectionService::class);
    $repository = app(RoomReservationRepository::class);

    // Mulai persis saat yang lain berakhir — tidak overlap.
    $adjacent = new DateRange(now()->addHours(4), now()->addHours(6));

    expect($service->hasConflict($repository->occupyingQueryForRoom($room->id), $adjacent))->toBeFalse();
});

it('ignores draft reservations when checking conflict', function () {
    $room = Room::factory()->create();

    RoomReservation::factory()->create([
        'room_id'        => $room->id,
        'status'         => ReservationStatus::Draft,
        'start_datetime' => now()->addHours(2),
        'end_datetime'   => now()->addHours(4),
    ]);

    $service = app(ConflictDetectionService::class);
    $repository = app(RoomReservationRepository::class);

    $overlapping = new DateRange(now()->addHours(3), now()->addHours(5));

    expect($service->hasConflict($repository->occupyingQueryForRoom($room->id), $overlapping))->toBeFalse();
});
