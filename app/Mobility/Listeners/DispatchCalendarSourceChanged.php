<?php

declare(strict_types=1);

namespace App\Mobility\Listeners;

use App\Calendar\Events\CalendarSourceChanged;

final class DispatchCalendarSourceChanged
{
    public function handle(object $event): void
    {
        event(new CalendarSourceChanged('vehicle_reservation'));
    }
}
