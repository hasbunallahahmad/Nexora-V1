<?php

declare(strict_types=1);

namespace App\Mobility\Models;

use App\Models\Agenda;
use App\Models\User;
use App\Shared\Enums\ReservationStatus;
use Database\Factories\Mobility\VehicleReservationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleReservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vehicle_reservations';

    protected $fillable = [
        'vehicle_id',
        'agenda_id',
        'requested_by',
        'approved_by',
        'title',
        'destination',
        'purpose',
        'start_datetime',
        'end_datetime',
        'actual_end_datetime',
        'status',
        'rejected_reason',
        'approved_at',
        'cancelled_at',
        'notes',
    ];

    protected $casts = [
        'status'         => ReservationStatus::class,
        'start_datetime' => 'datetime',
        'end_datetime'   => 'datetime',
        'actual_end_datetime'  => 'datetime',
        'approved_at'    => 'datetime',
        'cancelled_at'   => 'datetime',
    ];

    protected static function newFactory()
    {
        return VehicleReservationFactory::new();
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function agenda(): BelongsTo
    {
        return $this->belongsTo(Agenda::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
