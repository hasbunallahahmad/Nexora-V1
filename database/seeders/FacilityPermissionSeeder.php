<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class FacilityPermissionSeeder extends Seeder
{
    /**
     * Permission non-CRUD yang tidak otomatis dibuat oleh Filament Shield
     * `shield:generate` (yang hanya cover ability standar Policy).
     * CRUD permission (ViewAny:Room, Create:RoomReservation, dst) tetap
     * dibuat via `php artisan shield:generate` di Fase 5 (bersama Resource).
     */
    public function run(): void
    {
        $customPermissions = [
            'Approve:RoomReservation',
            'Reject:RoomReservation',
            'Cancel:RoomReservation',
        ];

        foreach ($customPermissions as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'web',
            ]);
        }
    }
}
