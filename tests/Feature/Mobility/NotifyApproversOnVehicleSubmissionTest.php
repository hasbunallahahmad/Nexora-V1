<?php

use App\Mobility\DTO\CreateVehicleReservationData;
use App\Mobility\Models\Vehicle;
use App\Mobility\Services\VehicleReservationService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('notifies only users holding the vehicle approve permission when a reservation is submitted', function () {
    $permission = Permission::firstOrCreate(['name' => 'Approve:VehicleReservation', 'guard_name' => 'web']);
    $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $role->givePermissionTo($permission);

    $approver = User::factory()->create();
    $approver->assignRole($role);

    $regularUser = User::factory()->create();

    $vehicle = Vehicle::factory()->create();

    $reservation = app(VehicleReservationService::class)->createDraft(new CreateVehicleReservationData(
        vehicleId: $vehicle->id,
        agendaId: null,
        requestedBy: $regularUser->id,
        title: 'Perjalanan dinas',
        destination: 'Jakarta',
        purpose: null,
        startDatetime: now()->addDay(),
        endDatetime: now()->addDay()->addHours(2),
    ));

    app(VehicleReservationService::class)->submit($reservation->id);

    expect($approver->fresh()->unreadNotifications()->count())->toBe(1)
        ->and($regularUser->fresh()->unreadNotifications()->count())->toBe(0);
});
