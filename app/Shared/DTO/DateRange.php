<?php

declare(strict_types=1);

namespace App\Shared\DTO;

use Carbon\CarbonInterface;

final readonly class DateRange
{
    public function __construct(
        public CarbonInterface $start,
        public CarbonInterface $end,
    ) {}

    public function overlaps(self $other): bool
    {
        return $this->start->lt($other->end) && $this->end->gt($other->start);
    }
}
