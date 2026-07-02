<?php

declare(strict_types=1);

namespace App\Calendar\DTO;

use Carbon\CarbonInterface;

final readonly class CalendarEventData
{
    public function __construct(
        public string $sourceType,
        public int|string $id,
        public string $title,
        public CarbonInterface $start,
        public ?CarbonInterface $end = null,
        public ?string $backgroundColor = null,
        public array $extendedProps = [],
    ) {}
}
