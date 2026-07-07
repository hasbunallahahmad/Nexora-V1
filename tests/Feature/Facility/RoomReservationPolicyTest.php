<?php

use App\Shared\Enums\ReservationStatus;
use App\Facility\Models\Room;
use App\Facility\Models\RoomReservation;
use App\Facility\Policies\RoomReservationPolicy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

it('allows the owner to update their own draft reservation without explicit permission', function () {
    $owner = User::factory()->create();
    $room = Room::factory()->create();

    $reservation = RoomReservation::factory()->create([
        'room_id'      => $room->id,
        'requested_by' => $owner->id,
        'status'       => ReservationStatus::Draft,
    ]);

    $policy = new RoomReservationPolicy();

    expect($policy->update($owner, $reservation))->toBeTrue();
});

it('denies update from a non-owner without explicit permission', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $room = Room::factory()->create();

    $reservation = RoomReservation::factory()->create([
        'room_id'      => $room->id,
        'requested_by' => $owner->id,
        'status'       => ReservationStatus::Draft,
    ]);

    $policy = new RoomReservationPolicy();

    expect($policy->update($stranger, $reservation))->toBeFalse();
});

it('denies owner from updating once the reservation is approved', function () {
    $owner = User::factory()->create();
    $room = Room::factory()->create();

    $reservation = RoomReservation::factory()->create([
        'room_id'      => $room->id,
        'requested_by' => $owner->id,
        'status'       => ReservationStatus::Approved,
    ]);

    $policy = new RoomReservationPolicy();

    expect($policy->update($owner, $reservation))->toBeFalse();
});

it('denies deleting an approved reservation even with delete permission', function () {
    Permission::firstOrCreate(['name' => 'Delete:RoomReservation', 'guard_name' => 'web']);

    $admin = User::factory()->create();
    $admin->givePermissionTo('Delete:RoomReservation');

    $room = Room::factory()->create();
    $reservation = RoomReservation::factory()->create([
        'room_id' => $room->id,
        'status'  => ReservationStatus::Approved,
    ]);

    $policy = new RoomReservationPolicy();

    expect($policy->delete($admin, $reservation))->toBeFalse();
});

it('allows deleting a draft reservation with delete permission', function () {
    Permission::firstOrCreate(['name' => 'Delete:RoomReservation', 'guard_name' => 'web']);

    $admin = User::factory()->create();
    $admin->givePermissionTo('Delete:RoomReservation');

    $room = Room::factory()->create();
    $reservation = RoomReservation::factory()->create([
        'room_id' => $room->id,
        'status'  => ReservationStatus::Draft,
    ]);

    $policy = new RoomReservationPolicy();

    expect($policy->delete($admin, $reservation))->toBeTrue();
});
