<?php

use App\Facility\DTO\ApproveReservationData;
use App\Shared\Enums\ReservationStatus;
use App\Facility\Enums\RoomStatus;
use App\Facility\Exceptions\ReservationConflictException;
use App\Facility\Exceptions\RoomNotReservableException;
use App\Facility\Services\RoomReservationService;
use App\Facility\Models\Room;
use App\Facility\Models\RoomReservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('rejects submit when it overlaps an already submitted reservation', function () {
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

    app(RoomReservationService::class)->submit($draft->id);
})->throws(ReservationConflictException::class);

it('allows submit when there is no overlap', function () {
    $room = Room::factory()->create();

    $draft = RoomReservation::factory()->create([
        'room_id'        => $room->id,
        'status'         => ReservationStatus::Draft,
        'start_datetime' => now()->addHours(2),
        'end_datetime'   => now()->addHours(4),
    ]);

    $result = app(RoomReservationService::class)->submit($draft->id);

    expect($result->status)->toBe(ReservationStatus::Submitted);
});

it('rejects submit when the room is under maintenance', function () {
    $room = Room::factory()->create(['status' => RoomStatus::Maintenance]);

    $draft = RoomReservation::factory()->create([
        'room_id'        => $room->id,
        'status'         => ReservationStatus::Draft,
        'start_datetime' => now()->addHours(2),
        'end_datetime'   => now()->addHours(4),
    ]);

    app(RoomReservationService::class)->submit($draft->id);
})->throws(RoomNotReservableException::class);

it('rejects approve when two submitted reservations overlap each other', function () {
    $room = Room::factory()->create();
    $approver = User::factory()->create();

    // Dua reservasi ini tidak overlap dengan siapa pun saat masing-masing
    // di-submit (dibuat via factory langsung berstatus Submitted, bukan
    // lewat submit()), tapi keduanya overlap satu sama lain.
    $first = RoomReservation::factory()->create([
        'room_id'        => $room->id,
        'status'         => ReservationStatus::Submitted,
        'start_datetime' => now()->addHours(2),
        'end_datetime'   => now()->addHours(4),
    ]);

    $second = RoomReservation::factory()->create([
        'room_id'        => $room->id,
        'status'         => ReservationStatus::Submitted,
        'start_datetime' => now()->addHours(3),
        'end_datetime'   => now()->addHours(5),
    ]);

    $service = app(RoomReservationService::class);
    $service->approve(new ApproveReservationData($first->id, $approver->id));

    // Reservasi kedua harus ditolak saat approve karena sudah bentrok
    // dengan yang pertama yang baru saja Approved.
    $service->approve(new ApproveReservationData($second->id, $approver->id));
})->throws(ReservationConflictException::class);
