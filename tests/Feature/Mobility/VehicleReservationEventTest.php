<?php

use App\Mobility\DTO\ApproveVehicleReservationData;
use App\Mobility\DTO\RejectVehicleReservationData;
use App\Mobility\Events\VehicleReservationApproved;
use App\Mobility\Events\VehicleReservationCancelled;
use App\Mobility\Events\VehicleReservationRejected;
use App\Mobility\Events\VehicleReservationSubmitted;
use App\Mobility\Exceptions\VehicleReservationConflictException;
use App\Mobility\Models\Vehicle;
use App\Mobility\Models\VehicleReservation;
use App\Mobility\Services\VehicleReservationService;
use App\Models\User;
use App\Shared\Enums\ReservationStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

it('dispatches VehicleReservationSubmitted when a draft is submitted', function () {
    Event::fake([VehicleReservationSubmitted::class]);

    $vehicle = Vehicle::factory()->create();
    $draft = VehicleReservation::factory()->create([
        'vehicle_id' => $vehicle->id,
        'status'     => ReservationStatus::Draft,
    ]);

    app(VehicleReservationService::class)->submit($draft->id);

    Event::assertDispatched(VehicleReservationSubmitted::class);
});

it('dispatches VehicleReservationApproved when a submitted reservation is approved', function () {
    Event::fake([VehicleReservationApproved::class]);

    $vehicle = Vehicle::factory()->create();
    $approver = User::factory()->create();
    $submitted = VehicleReservation::factory()->create([
        'vehicle_id' => $vehicle->id,
        'status'     => ReservationStatus::Submitted,
    ]);

    app(VehicleReservationService::class)->approve(new ApproveVehicleReservationData($submitted->id, $approver->id));

    Event::assertDispatched(VehicleReservationApproved::class);
});

it('dispatches VehicleReservationRejected when a submitted reservation is rejected', function () {
    Event::fake([VehicleReservationRejected::class]);

    $vehicle = Vehicle::factory()->create();
    $approver = User::factory()->create();
    $submitted = VehicleReservation::factory()->create([
        'vehicle_id' => $vehicle->id,
        'status'     => ReservationStatus::Submitted,
    ]);

    app(VehicleReservationService::class)->reject(new RejectVehicleReservationData($submitted->id, $approver->id, 'Kendaraan diprioritaskan untuk keperluan lain'));

    Event::assertDispatched(VehicleReservationRejected::class);
});

it('dispatches VehicleReservationCancelled when an approved reservation is cancelled', function () {
    Event::fake([VehicleReservationCancelled::class]);

    $vehicle = Vehicle::factory()->create();
    $approved = VehicleReservation::factory()->create([
        'vehicle_id' => $vehicle->id,
        'status'     => ReservationStatus::Approved,
    ]);

    app(VehicleReservationService::class)->cancel($approved->id);

    Event::assertDispatched(VehicleReservationCancelled::class);
});

it('does not dispatch VehicleReservationSubmitted when submit fails due to conflict', function () {
    Event::fake([VehicleReservationSubmitted::class]);

    $vehicle = Vehicle::factory()->create();

    VehicleReservation::factory()->create([
        'vehicle_id'     => $vehicle->id,
        'status'         => ReservationStatus::Submitted,
        'start_datetime' => now()->addHours(2),
        'end_datetime'   => now()->addHours(4),
    ]);

    $draft = VehicleReservation::factory()->create([
        'vehicle_id'     => $vehicle->id,
        'status'         => ReservationStatus::Draft,
        'start_datetime' => now()->addHours(3),
        'end_datetime'   => now()->addHours(5),
    ]);

    try {
        app(VehicleReservationService::class)->submit($draft->id);
    } catch (VehicleReservationConflictException) {
        // expected
    }

    Event::assertNotDispatched(VehicleReservationSubmitted::class);
});
