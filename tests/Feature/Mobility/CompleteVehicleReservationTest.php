<?php

use App\Mobility\DTO\CompleteVehicleReservationData;
use App\Mobility\Exceptions\InvalidVehicleReservationTransitionException;
use App\Mobility\Models\Vehicle;
use App\Mobility\Models\VehicleReservation;
use App\Mobility\Policies\VehicleReservationPolicy;
use App\Mobility\Services\VehicleReservationService;
use App\Models\User;
use App\Shared\Enums\ReservationStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows completing an approved reservation with an actual end time', function () {
    $vehicle = Vehicle::factory()->create();
    $requester = User::factory()->create();

    $reservation = VehicleReservation::factory()->create([
        'vehicle_id'     => $vehicle->id,
        'requested_by'   => $requester->id,
        'status'         => ReservationStatus::Approved,
        'start_datetime' => now()->subHours(3),
        'end_datetime'   => now()->subHour(),
    ]);

    $actualEnd = now();

    $result = app(VehicleReservationService::class)->complete(
        new CompleteVehicleReservationData($reservation->id, $requester->id, $actualEnd)
    );

    expect($result->status)->toBe(ReservationStatus::Completed)
        ->and($result->actual_end_datetime->timestamp)->toBe($actualEnd->timestamp);
});

it('rejects completing a reservation that is not approved', function () {
    $vehicle = Vehicle::factory()->create();
    $reservation = VehicleReservation::factory()->create([
        'vehicle_id' => $vehicle->id,
        'status'     => ReservationStatus::Draft,
    ]);

    app(VehicleReservationService::class)->complete(
        new CompleteVehicleReservationData($reservation->id, 1, now())
    );
})->throws(InvalidVehicleReservationTransitionException::class);

it('rejects an actual end time before the start time', function () {
    $vehicle = Vehicle::factory()->create();
    $reservation = VehicleReservation::factory()->create([
        'vehicle_id'     => $vehicle->id,
        'status'         => ReservationStatus::Approved,
        'start_datetime' => now(),
        'end_datetime'   => now()->addHours(2),
    ]);

    app(VehicleReservationService::class)->complete(
        new CompleteVehicleReservationData($reservation->id, 1, now()->subDay())
    );
})->throws(InvalidArgumentException::class);

it('allows the owner to complete their own reservation without explicit permission', function () {
    $owner = User::factory()->create();
    $vehicle = Vehicle::factory()->create();

    $reservation = VehicleReservation::factory()->create([
        'vehicle_id'   => $vehicle->id,
        'requested_by' => $owner->id,
        'status'       => ReservationStatus::Approved,
    ]);

    $policy = new VehicleReservationPolicy();

    expect($policy->complete($owner, $reservation))->toBeTrue();
});

it('denies a non-owner from completing without explicit permission', function () {
    $stranger = User::factory()->create();
    $vehicle = Vehicle::factory()->create();

    $reservation = VehicleReservation::factory()->create([
        'vehicle_id'   => $vehicle->id,
        'requested_by' => User::factory()->create()->id,
        'status'       => ReservationStatus::Approved,
    ]);

    $policy = new VehicleReservationPolicy();

    expect($policy->complete($stranger, $reservation))->toBeFalse();
});
