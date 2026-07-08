<?php

use App\Mobility\Models\Vehicle;
use App\Mobility\Models\VehicleReservation;
use App\Models\User;
use App\Shared\Enums\ReservationStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('prevents even super_admin from deleting an approved vehicle reservation', function () {
    $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole($superAdminRole);

    $vehicle = Vehicle::factory()->create();
    $reservation = VehicleReservation::factory()->create([
        'vehicle_id' => $vehicle->id,
        'status'     => ReservationStatus::Approved,
    ]);

    expect($superAdmin->can('delete', $reservation))->toBeFalse();
});
