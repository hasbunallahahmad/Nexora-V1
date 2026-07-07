<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MobilityPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $customPermissions = [
            'Approve:VehicleReservation',
            'Reject:VehicleReservation',
            'Cancel:VehicleReservation',
        ];

        foreach ($customPermissions as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'web',
            ]);
        }

        $superAdmin = Role::where('name', 'super_admin')->first();

        if ($superAdmin) {
            $superAdmin->givePermissionTo($customPermissions);
        }
    }
}
