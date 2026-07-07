<?php

declare(strict_types=1);

namespace App\Facility\Providers;

use App\Facility\Events\ReservationApproved;
use App\Facility\Events\ReservationCancelled;
use App\Facility\Events\ReservationRejected;
use App\Facility\Events\ReservationSubmitted;
use App\Facility\Listeners\DispatchCalendarSourceChanged;
use App\Facility\Listeners\LogReservationDecision;
use App\Facility\Listeners\LogReservationSubmitted;
use App\Facility\Models\Room;
use App\Facility\Models\RoomReservation;
use App\Facility\Policies\RoomPolicy;
use App\Facility\Policies\RoomReservationPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class FacilityServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected array $policies = [
        Room::class => RoomPolicy::class,
        RoomReservation::class => RoomReservationPolicy::class,
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

        $calendarRelevantEvents = [
            ReservationSubmitted::class,
            ReservationApproved::class,
            ReservationRejected::class,
            ReservationCancelled::class,
        ];

        foreach ($calendarRelevantEvents as $event) {
            Event::listen($event, DispatchCalendarSourceChanged::class);
        }

        // Event::listen(ReservationSubmitted::class, LogReservationSubmitted::class);
        Event::listen(ReservationSubmitted::class, \App\Facility\Listeners\NotifyApproversOnSubmission::class);
        Event::listen(ReservationApproved::class, LogReservationDecision::class);
        Event::listen(ReservationRejected::class, LogReservationDecision::class);
    }
}
