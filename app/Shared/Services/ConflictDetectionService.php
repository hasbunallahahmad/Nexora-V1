<?php

declare(strict_types=1);

namespace App\Shared\Services;

use App\Shared\DTO\DateRange;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class ConflictDetectionService
{
    public function hasConflict(Builder $occupyingQuery, DateRange $range, ?int $excludeId = null): bool
    {
        return $this->conflictQuery($occupyingQuery, $range, $excludeId)->exists();
    }

    public function findConflicts(Builder $occupyingQuery, DateRange $range, ?int $excludeId = null): Collection
    {
        return $this->conflictQuery($occupyingQuery, $range, $excludeId)->get();
    }

    private function conflictQuery(Builder $occupyingQuery, DateRange $range, ?int $excludeId): Builder
    {
        $query = (clone $occupyingQuery)
            ->where('start_datetime', '<', $range->end)
            ->where('end_datetime', '>', $range->start);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query;
    }
}
