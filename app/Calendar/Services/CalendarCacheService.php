<?php

declare(strict_types=1);

namespace App\Calendar\Services;

use Closure;
use Illuminate\Support\Facades\Cache;

final class CalendarCacheService
{
    private const VERSION_KEY = 'kalendar_cache_version';

    public function version(): int
    {
        return (int) Cache::get(self::VERSION_KEY, 0);
    }

    public function invalidate(): void
    {
        Cache::put(self::VERSION_KEY, $this->version() + 1, now()->addDays(90));
    }

    public function remember(string $key, Closure $callback, int $ttlHours = 24): mixed
    {
        $versionedKey = sprintf('%s_v%d', $key, $this->version());

        return Cache::remember($versionedKey, now()->addHours($ttlHours), $callback);
    }
}
