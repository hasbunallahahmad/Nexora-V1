<?php

use App\Calendar\DTO\CalendarQuery;
use App\Calendar\Enums\CalendarAudience;
use App\Mobility\Models\Vehicle;
use App\Mobility\Models\VehicleReservation;
use App\Mobility\Services\Sources\VehicleReservationEventSource;
use App\Shared\Enums\ReservationStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('excludes submitted vehicle reservations from the public audience', function () {
    $vehicle = Vehicle::factory()->create();

    $approved = VehicleReservation::factory()->create([
        'vehicle_id'     => $vehicle->id,
        'status'         => ReservationStatus::Approved,
        'start_datetime' => now()->addDay(),
        'end_datetime'   => now()->addDay()->addHours(2),
    ]);

    VehicleReservation::factory()->create([
        'vehicle_id'     => $vehicle->id,
        'status'         => ReservationStatus::Submitted,
        'start_datetime' => now()->addDays(2),
        'end_datetime'   => now()->addDays(2)->addHours(2),
    ]);

    $query = new CalendarQuery(now(), now()->addWeek(), CalendarAudience::Public);
    $events = (new VehicleReservationEventSource())->events($query);

    expect($events)->toHaveCount(1)
        ->and($events->first()->id)->toBe($approved->id);
});

it('includes submitted vehicle reservations for the admin audience', function () {
    $vehicle = Vehicle::factory()->create();

    VehicleReservation::factory()->create([
        'vehicle_id'     => $vehicle->id,
        'status'         => ReservationStatus::Submitted,
        'start_datetime' => now()->addDay(),
        'end_datetime'   => now()->addDay()->addHours(2),
    ]);

    $query = new CalendarQuery(now(), now()->addWeek(), CalendarAudience::Admin);
    $events = (new VehicleReservationEventSource())->events($query);

    expect($events)->toHaveCount(1);
});
