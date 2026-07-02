<?php

use App\Models\User;
use Tapp\FilamentAuthenticationLog\Notifications\NewDevice;
use Tapp\FilamentAuthenticationLog\Resources\AuthenticationLogResource;

return [
    // 'user-resource' => \App\Filament\Resources\UserResource::class,
    'resources' => [
        'AutenticationLogResource' => AuthenticationLogResource::class,
    ],

    'authenticable-resources' => [
        User::class,
    ],

    'authenticatable' => [
        'field-to-display' => 'name',
    ],

    'navigation' => [
        'authentication-log' => [
            'register' => true,
            'sort' => 1,
            'icon' => 'heroicon-o-shield-check',
            'group' => 'System',
        ],
    ],

    'sort' => [
        'column' => 'login_at',
        'direction' => 'desc',
    ],

    'authenticatable_models' => [
        User::class,
    ],

    'events' => [
        'login' => \Illuminate\Auth\Events\Login::class,
        'logout' => \Illuminate\Auth\Events\Logout::class,
        'failed' => \Illuminate\Auth\Events\Failed::class,
    ],
    'location' => [
        'enabled' => false,
    ],
    'notifications' => [
        'new-device' => [
            'enabled' => true,
        ],
    ],
];
