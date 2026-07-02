<?php

use App\Calendar\Services\CalendarCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('does not bump calendar cache version on polling without data change', function () {
    $cache = app(CalendarCacheService::class);
    $before = $cache->version();

    $this->getJson(route('api.agenda.polling'))->assertOk();

    expect($cache->version())->toBe($before);
});
