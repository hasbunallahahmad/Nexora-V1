<?php

use Database\Seeders\MobilityPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('assigns approve, reject, and cancel permissions to super_admin role', function () {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

    (new MobilityPermissionSeeder())->run();

    $superAdmin = Role::where('name', 'super_admin')->first();

    expect($superAdmin->hasPermissionTo('Approve:VehicleReservation'))->toBeTrue()
        ->and($superAdmin->hasPermissionTo('Reject:VehicleReservation'))->toBeTrue()
        ->and($superAdmin->hasPermissionTo('Cancel:VehicleReservation'))->toBeTrue();
});
