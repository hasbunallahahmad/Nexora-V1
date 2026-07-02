<?php

namespace App\Filament\Resources\Agendas\Pages;

use App\Filament\Resources\Agendas\AgendaResource;
use App\Services\AgendaService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAgenda extends CreateRecord
{
    protected static string $resource = AgendaResource::class;

    private AgendaService $agendaService;

    public function __construct()
    {
        parent::__construct();
        $this->agendaService = new AgendaService();
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Sanitize and validate data using AgendaService
        $data = $this->agendaService->sanitize($data);

        // Handle bidang_id relationship
        $bidangIds = $data['bidang_id'] ?? [];
        unset($data['bidang_id']);

        // Create the agenda record
        $record = $this->getModel()::create($data);

        // Attach bidang relationships
        if (!empty($bidangIds)) {
            $record->bidang()->attach($bidangIds);
        }

        return $record;
    }
}
