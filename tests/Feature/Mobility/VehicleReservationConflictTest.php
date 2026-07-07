<?php

use App\Mobility\DTO\ApproveVehicleReservationData;
use App\Mobility\Exceptions\VehicleMaintenanceConflictException;
use App\Mobility\Exceptions\VehicleNotReservableException;
use App\Mobility\Exceptions\VehicleReservationConflictException;
use App\Mobility\Models\Vehicle;
use App\Mobility\Models\VehicleMaintenancePeriod;
use App\Mobility\Models\VehicleReservation;
use App\Mobility\Services\VehicleReservationService;
use App\Models\User;
use App\Shared\Enums\ReservationStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('rejects submit when it overlaps an already submitted vehicle reservation', function () {
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

    app(VehicleReservationService::class)->submit($draft->id);
})->throws(VehicleReservationConflictException::class);

it('rejects submit when it overlaps a scheduled maintenance period', function () {
    $vehicle = Vehicle::factory()->create();

    VehicleMaintenancePeriod::factory()->create([
        'vehicle_id'     => $vehicle->id,
        'start_datetime' => now()->addHours(2),
        'end_datetime'   => now()->addHours(6),
    ]);

    $draft = VehicleReservation::factory()->create([
        'vehicle_id'     => $vehicle->id,
        'status'         => ReservationStatus::Draft,
        'start_datetime' => now()->addHours(3),
        'end_datetime'   => now()->addHours(5),
    ]);

    app(VehicleReservationService::class)->submit($draft->id);
})->throws(VehicleMaintenanceConflictException::class);

it('allows submit when there is no overlap with reservations or maintenance', function () {
    $vehicle = Vehicle::factory()->create();

    VehicleMaintenancePeriod::factory()->create([
        'vehicle_id'     => $vehicle->id,
        'start_datetime' => now()->addDays(5),
        'end_datetime'   => now()->addDays(6),
    ]);

    $draft = VehicleReservation::factory()->create([
        'vehicle_id'     => $vehicle->id,
        'status'         => ReservationStatus::Draft,
        'start_datetime' => now()->addHours(2),
        'end_datetime'   => now()->addHours(4),
    ]);

    $result = app(VehicleReservationService::class)->submit($draft->id);

    expect($result->status)->toBe(ReservationStatus::Submitted);
});

it('rejects submit when the vehicle is inactive', function () {
    $vehicle = Vehicle::factory()->create(['status' => \App\Mobility\Enums\VehicleStatus::Inactive]);

    $draft = VehicleReservation::factory()->create([
        'vehicle_id'     => $vehicle->id,
        'status'         => ReservationStatus::Draft,
        'start_datetime' => now()->addHours(2),
        'end_datetime'   => now()->addHours(4),
    ]);

    app(VehicleReservationService::class)->submit($draft->id);
})->throws(VehicleNotReservableException::class);

it('rejects approve when two submitted reservations overlap each other', function () {
    $vehicle = Vehicle::factory()->create();
    $approver = User::factory()->create();

    $first = VehicleReservation::factory()->create([
        'vehicle_id'     => $vehicle->id,
        'status'         => ReservationStatus::Submitted,
        'start_datetime' => now()->addHours(2),
        'end_datetime'   => now()->addHours(4),
    ]);

    $second = VehicleReservation::factory()->create([
        'vehicle_id'     => $vehicle->id,
        'status'         => ReservationStatus::Submitted,
        'start_datetime' => now()->addHours(3),
        'end_datetime'   => now()->addHours(5),
    ]);

    $service = app(VehicleReservationService::class);
    $service->approve(new ApproveVehicleReservationData($first->id, $approver->id));
    $service->approve(new ApproveVehicleReservationData($second->id, $approver->id));
})->throws(VehicleReservationConflictException::class);

it('rejects approve when a maintenance period is scheduled after submission but before approval', function () {
    $vehicle = Vehicle::factory()->create();
    $approver = User::factory()->create();

    $submitted = VehicleReservation::factory()->create([
        'vehicle_id'     => $vehicle->id,
        'status'         => ReservationStatus::Submitted,
        'start_datetime' => now()->addHours(2),
        'end_datetime'   => now()->addHours(4),
    ]);

    // Maintenance dijadwalkan belakangan, setelah reservasi di-submit —
    // membuktikan re-check di approve() menangkap perubahan kondisi baru.
    VehicleMaintenancePeriod::factory()->create([
        'vehicle_id'     => $vehicle->id,
        'start_datetime' => now()->addHours(3),
        'end_datetime'   => now()->addHours(5),
    ]);

    app(VehicleReservationService::class)->approve(new ApproveVehicleReservationData($submitted->id, $approver->id));
})->throws(VehicleMaintenanceConflictException::class);
