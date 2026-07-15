<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('sets must_change_password to true for a newly created user', function () {
    $role = Role::firstOrCreate(['name' => 'admin_test', 'guard_name' => 'web']);

    $user = User::create([
        'name'                  => 'Test User Baru',
        'email'                 => 'usertest@dev.dev',
        'password'              => Hash::make('temporary-password'),
        'email_verified_at'     => now(),
        'must_change_password'  => true,
        'is_active'             => true,
    ]);

    $user->roles()->sync([$role->id]);

    expect($user->must_change_password)->toBeTrue()
        ->and($user->roles->pluck('name'))->toContain('admin_test');
});

it('clears must_change_password automatically when the password is changed', function () {
    $user = User::create([
        'name'                  => 'Test User',
        'email'                 => 'usertest2@dev.dev',
        'password'              => Hash::make('old-password'),
        'email_verified_at'     => now(),
        'must_change_password'  => true,
        'is_active'             => true,
    ]);

    $user->update(['password' => Hash::make('new-strong-password')]);

    expect($user->fresh()->must_change_password)->toBeFalse();
});
