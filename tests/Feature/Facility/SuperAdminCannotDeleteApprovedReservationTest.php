<?php

use App\Facility\Models\Room;
use App\Facility\Models\RoomReservation;
use App\Models\User;
use App\Shared\Enums\ReservationStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('prevents even super_admin from deleting an approved room reservation', function () {
    $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole($superAdminRole);

    $room = Room::factory()->create();
    $reservation = RoomReservation::factory()->create([
        'room_id' => $room->id,
        'status'  => ReservationStatus::Approved,
    ]);

    expect($superAdmin->can('delete', $reservation))->toBeFalse();
});
