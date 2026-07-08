<?php

declare(strict_types=1);

namespace App\Mobility\Filament\Resources\Vehicles\Pages;

use App\Mobility\Filament\Resources\Vehicles\VehicleResource;
use Filament\Resources\Pages\ListRecords;

class ListVehicles extends ListRecords
{
    protected static string $resource = VehicleResource::class;
}
