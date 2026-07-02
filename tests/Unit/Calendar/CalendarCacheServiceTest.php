<?php

use App\Calendar\Services\CalendarCacheService;

uses(Tests\TestCase::class);


it('starts at version 0 and increments on invalidate', function () {
    $service = new CalendarCacheService();

    expect($service->version())->toBe(0);

    $service->invalidate();

    expect($service->version())->toBe(1);
});

it('does not recompute when version is unchanged', function () {
    $service = new CalendarCacheService();
    $calls = 0;

    $service->remember('test_key', function () use (&$calls) {
        $calls++;
        return 'value';
    });

    $service->remember('test_key', function () use (&$calls) {
        $calls++;
        return 'value';
    });

    expect($calls)->toBe(1);
});

it('recomputes after invalidate bumps the version', function () {
    $service = new CalendarCacheService();
    $calls = 0;

    $callback = function () use (&$calls) {
        $calls++;
        return 'value';
    };

    $service->remember('test_key', $callback);
    $service->invalidate();
    $service->remember('test_key', $callback);

    expect($calls)->toBe(2);
});
