<?php

declare(strict_types=1);

namespace App\Facility\Models;

use App\Models\Agenda;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomReservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'room_reservations';

    protected $fillable = [
        'room_id',
        'agenda_id',
        'requested_by',
        'approved_by',
        'title',
        'purpose',
        'start_datetime',
        'end_datetime',
        'status',
        'rejected_reason',
        'approved_at',
        'cancelled_at',
        'notes',
        'guest_name',
        'guest_contact',
        'guest_instansi',
    ];

    protected $casts = [
        'status' => \App\Shared\Enums\ReservationStatus::class,
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'approved_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
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

    protected static function newFactory()
    {
        return \Database\Factories\Facility\RoomReservationFactory::new();
    }
}
