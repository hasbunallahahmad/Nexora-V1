<?php

declare(strict_types=1);

namespace App\Facility\Models;

use App\Facility\Enums\RoomStatus;
use App\Facility\Models\RoomReservation;
use App\Shared\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Room extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rooms';

    protected $fillable = [
        'name',
        'slug',
        'location',
        'capacity',
        'facilities',
        'status',
        'photo_path',
    ];

    protected $casts = [
        'status' => RoomStatus::class,
        'facilities' => 'array',
        'capacity' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating((function (self $room): void {
            if (empty($room->slug)) {
                $room->slug = Str::slug($room->name);
            }
        }));

        static::updating(function (self $room): void {
            if ($room->isDirty('name') && empty($room->getOriginal('slug'))) {
                $room->slug = Str::slug($room->name);
            }
        });
    }

    public function getRouteKeyname(): string
    {
        return 'slug';
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(RoomReservation::class);
    }

    public function occupyingReservations(): HasMany
    {
        return $this->reservations()->whereIn(
            'status',
            array_map(fn($s) => $s->value, ReservationStatus::occupyingStatuses())
        );
    }

    protected static function newFactory()
    {
        return \Database\Factories\Facility\RoomFactory::new();
    }
}
