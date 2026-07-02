<?php

namespace App\Filament\Resources\Agendas\Pages;

use App\Filament\Resources\Agendas\AgendaResource;
use App\Services\AgendaService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditAgenda extends EditRecord
{
    protected static string $resource = AgendaResource::class;

    private AgendaService $agendaService;

    public function __construct()
    {
        parent::__construct();
        $this->agendaService = new AgendaService();
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Sanitize and validate data using AgendaService
        $data = $this->agendaService->sanitize($data);
        
        // Handle bidang_id relationship
        $bidangIds = $data['bidang_id'] ?? [];
        unset($data['bidang_id']);

        // Update the agenda record
        $record->update($data);

        // Sync bidang relationships
        $record->bidang()->sync($bidangIds);

        return $record->fresh();
    }
}
