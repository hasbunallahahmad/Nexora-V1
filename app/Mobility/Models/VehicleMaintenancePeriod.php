<?php

declare(strict_types=1);

namespace App\Mobility\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleMaintenancePeriod extends Model
{
    use HasFactory;

    protected $table = 'vehicle_maintenance_periods';

    protected $fillable = [
        'vehicle_id',
        'start_datetime',
        'end_datetime',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime'   => 'datetime',
    ];

    protected static function newFactory()
    {
        return \Database\Factories\Mobility\VehicleMaintenancePeriodFactory::new();
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
