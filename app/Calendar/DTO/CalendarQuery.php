<?php

declare(strict_types=1);

namespace App\Calendar\DTO;

use App\Calendar\Enums\CalendarAudience;
use Carbon\CarbonInterface;

final readonly class CalendarQuery
{
    public function __construct(
        public CarbonInterface $start,
        public CarbonInterface $end,
        public CalendarAudience $audience,
    ) {}

    public function cacheKey(): string
    {
        return sprintf(
            '%s_%s_%s',
            $this->start->toDateString(),
            $this->end->toDateString(),
            $this->audience->value,
        );
    }
}
