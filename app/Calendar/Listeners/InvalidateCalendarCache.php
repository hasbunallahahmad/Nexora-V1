<?php

declare(strict_types=1);

namespace App\Calendar\Listeners;

use App\Calendar\Events\CalendarSourceChanged;
use App\Calendar\Services\CalendarCacheService;

final class InvalidateCalendarCache
{
    public function __construct(
        private readonly CalendarCacheService $cache,
    ) {}

    public function handle(CalendarSourceChanged $event): void
    {
        $this->cache->invalidate();
    }
}
