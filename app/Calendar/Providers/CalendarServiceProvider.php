<?php

declare(strict_types=1);

namespace App\Calendar\Providers;

use App\Calendar\Contracts\CalendarEventSource;
use App\Calendar\Events\CalendarSourceChanged;
use App\Calendar\Listeners\InvalidateCalendarCache;
use App\Calendar\Services\CalendarAggregationService;
use App\Calendar\Services\CalendarCacheService;
use App\Calendar\Services\Sources\ActivityEventSource;
use App\Facility\Services\Sources\RoomReservationEventSource;
use App\Mobility\Services\Sources\VehicleReservationEventSource;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class CalendarServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CalendarCacheService::class);

        $this->app->tag([
            ActivityEventSource::class,
            RoomReservationEventSource::class,
            VehicleReservationEventSource::class,
        ], CalendarEventSource::class);

        $this->app->singleton(CalendarAggregationService::class, fn($app) => new CalendarAggregationService(
            sources: $app->tagged(CalendarEventSource::class),
            cache: $app->make(CalendarCacheService::class),
        ));
    }

    public function boot(): void
    {
        Event::listen(CalendarSourceChanged::class, InvalidateCalendarCache::class);
    }
}
