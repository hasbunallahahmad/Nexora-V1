<?php

use App\Facility\DTO\CreateRoomReservationData;
use App\Facility\Models\Room;
use App\Facility\Services\RoomReservationService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('notifies only users holding the approve permission when a reservation is submitted', function () {
    $permission = Permission::firstOrCreate(['name' => 'Approve:RoomReservation', 'guard_name' => 'web']);
    $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $role->givePermissionTo($permission);

    $approver = User::factory()->create();
    $approver->assignRole($role);

    $regularUser = User::factory()->create();

    $room = Room::factory()->create();

    $reservation = app(RoomReservationService::class)->createDraft(new CreateRoomReservationData(
        roomId: $room->id,
        agendaId: null,
        requestedBy: $regularUser->id,
        title: 'Rapat koordinasi',
        purpose: null,
        startDatetime: now()->addDay(),
        endDatetime: now()->addDay()->addHours(2),
    ));

    app(RoomReservationService::class)->submit($reservation->id);

    expect($approver->fresh()->unreadNotifications()->count())->toBe(1)
        ->and($regularUser->fresh()->unreadNotifications()->count())->toBe(0);
});
