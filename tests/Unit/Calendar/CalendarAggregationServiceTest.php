<?php

use App\Calendar\Contracts\CalendarEventSource;
use App\Calendar\DTO\CalendarEventData;
use App\Calendar\DTO\CalendarQuery;
use App\Calendar\Enums\CalendarAudience;
use App\Calendar\Services\CalendarAggregationService;
use App\Calendar\Services\CalendarCacheService;
use Illuminate\Support\Collection;

uses(Tests\TestCase::class);

it('merges and sorts events from multiple sources', function () {
    $sourceA = new class implements CalendarEventSource {
        public function sourceType(): string
        {
            return 'a';
        }
        public function events(CalendarQuery $query): Collection
        {
            return collect([
                new CalendarEventData('a', 1, 'Event A', now()->addDays(2)),
            ]);
        }
    };

    $sourceB = new class implements CalendarEventSource {
        public function sourceType(): string
        {
            return 'b';
        }
        public function events(CalendarQuery $query): Collection
        {
            return collect([
                new CalendarEventData('b', 1, 'Event B', now()->addDay()),
            ]);
        }
    };

    $service = new CalendarAggregationService([$sourceA, $sourceB], new CalendarCacheService());

    $query = new CalendarQuery(now(), now()->addWeek(), CalendarAudience::Public);
    $events = $service->getEvents($query);

    expect($events)->toHaveCount(2)
        ->and($events->first()->title)->toBe('Event B'); // lebih awal, harus di atas
});
