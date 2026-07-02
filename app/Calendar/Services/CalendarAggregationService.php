<?php

declare(strict_types=1);

namespace App\Calendar\Services;

use App\Calendar\Contracts\CalendarEventSource;
use App\Calendar\DTO\CalendarEventData;
use App\Calendar\DTO\CalendarQuery;
use Illuminate\Support\Collection;

final class CalendarAggregationService
{
    /**
     * @param iterable<CalendarEventSource> $sources
     */
    public function __construct(
        private readonly iterable $sources,
        private readonly CalendarCacheService $cache,
    ) {}

    /**
     * @return Collection<int, CalendarEventData>
     */
    public function getEvents(CalendarQuery $query): Collection
    {
        return $this->cache->remember(
            'calendar_' . $query->cacheKey(),
            function () use ($query): Collection {
                $events = collect();

                foreach ($this->sources as $source) {
                    $events = $events->merge($source->events($query));
                }

                return $events
                    ->sortBy(fn(CalendarEventData $event) => $event->start)
                    ->values();
            },
        );
    }
}
