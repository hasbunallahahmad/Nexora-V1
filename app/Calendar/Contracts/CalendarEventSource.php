<?php

declare(strict_types=1);

namespace App\Calendar\Contracts;

use App\Calendar\DTO\CalendarQuery;
use Illuminate\Support\Collection;

interface CalendarEventSource
{
    public function sourceType(): string;

    public function events(CalendarQuery $query): Collection;
}
