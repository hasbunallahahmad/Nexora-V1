<?php

use App\Mobility\Models\Vehicle;
use App\Mobility\Models\VehicleReservation;
use App\Mobility\Policies\VehicleReservationPolicy;
use App\Models\User;
use App\Shared\Enums\ReservationStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

it('allows the owner to update their own draft vehicle reservation without explicit permission', function () {
    $owner = User::factory()->create();
    $vehicle = Vehicle::factory()->create();

    $reservation = VehicleReservation::factory()->create([
        'vehicle_id'   => $vehicle->id,
        'requested_by' => $owner->id,
        'status'       => ReservationStatus::Draft,
    ]);

    $policy = new VehicleReservationPolicy();

    expect($policy->update($owner, $reservation))->toBeTrue();
});

it('denies update from a non-owner without explicit permission', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $vehicle = Vehicle::factory()->create();

    $reservation = VehicleReservation::factory()->create([
        'vehicle_id'   => $vehicle->id,
        'requested_by' => $owner->id,
        'status'       => ReservationStatus::Draft,
    ]);

    $policy = new VehicleReservationPolicy();

    expect($policy->update($stranger, $reservation))->toBeFalse();
});

it('denies owner from updating once the vehicle reservation is approved', function () {
    $owner = User::factory()->create();
    $vehicle = Vehicle::factory()->create();

    $reservation = VehicleReservation::factory()->create([
        'vehicle_id'   => $vehicle->id,
        'requested_by' => $owner->id,
        'status'       => ReservationStatus::Approved,
    ]);

    $policy = new VehicleReservationPolicy();

    expect($policy->update($owner, $reservation))->toBeFalse();
});

it('denies deleting an approved vehicle reservation even with delete permission', function () {
    Permission::firstOrCreate(['name' => 'Delete:VehicleReservation', 'guard_name' => 'web']);

    $admin = User::factory()->create();
    $admin->givePermissionTo('Delete:VehicleReservation');

    $vehicle = Vehicle::factory()->create();
    $reservation = VehicleReservation::factory()->create([
        'vehicle_id' => $vehicle->id,
        'status'     => ReservationStatus::Approved,
    ]);

    $policy = new VehicleReservationPolicy();

    expect($policy->delete($admin, $reservation))->toBeFalse();
});

it('allows deleting a draft vehicle reservation with delete permission', function () {
    Permission::firstOrCreate(['name' => 'Delete:VehicleReservation', 'guard_name' => 'web']);

    $admin = User::factory()->create();
    $admin->givePermissionTo('Delete:VehicleReservation');

    $vehicle = Vehicle::factory()->create();
    $reservation = VehicleReservation::factory()->create([
        'vehicle_id' => $vehicle->id,
        'status'     => ReservationStatus::Draft,
    ]);

    $policy = new VehicleReservationPolicy();

    expect($policy->delete($admin, $reservation))->toBeTrue();
});
