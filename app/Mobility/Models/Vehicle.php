<?php

declare(strict_types=1);

namespace App\Mobility\Models;

use App\Mobility\Enums\VehicleStatus;
use App\Mobility\Models\VehicleReservation;
use App\Shared\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vehicles';

    protected $fillable = [
        'name',
        'plate_number',
        'type',
        'capacity',
        'driver_name',
        'driver_contact',
        'status',
        'photo_path',
    ];

    protected $casts = [
        'status'   => VehicleStatus::class,
        'capacity' => 'integer',
    ];

    protected static function newFactory()
    {
        return \Database\Factories\Mobility\VehicleFactory::new();
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(VehicleReservation::class);
    }

    public function maintenancePeriods(): HasMany
    {
        return $this->hasMany(VehicleMaintenancePeriod::class);
    }

    /**
     * Reservasi yang sedang "menempati" jadwal (Submitted/Approved) —
     * dipakai oleh ConflictDetectionService.
     */
    public function occupyingReservations(): HasMany
    {
        return $this->reservations()->whereIn(
            'status',
            array_map(fn($s) => $s->value, ReservationStatus::occupyingStatuses())
        );
    }
}
