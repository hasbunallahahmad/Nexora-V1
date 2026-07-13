<?php

declare(strict_types=1);

namespace App\Activity\Providers;

use App\Activity\Models\Agenda as ActivityAgenda;
use App\Activity\Policies\AgendaPolicy;
// use App\Models\Agenda as LegacyAgenda;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class ActivityServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected array $policies = [
        ActivityAgenda::class => AgendaPolicy::class,
        // LegacyAgenda::class => AgendaPolicy::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
