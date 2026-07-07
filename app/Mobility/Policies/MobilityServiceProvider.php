<?php

declare(strict_types=1);

namespace App\Mobility\Providers;

use App\Mobility\Models\Vehicle;
use App\Mobility\Models\VehicleReservation;
use App\Mobility\Policies\VehiclePolicy;
use App\Mobility\Policies\VehicleReservationPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class MobilityServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected array $policies = [
        Vehicle::class => VehiclePolicy::class,
        VehicleReservation::class => VehicleReservationPolicy::class,
    ];

    public function register(): void
    {
        // Repositories, Services, Actions memakai concrete class dengan
        // constructor typed — Laravel container autowire tanpa binding manual.
    }

    public function boot(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
