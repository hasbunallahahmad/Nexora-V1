<?php

namespace App\Filament\Resources\Bidangs\Pages;

use App\Filament\Resources\Bidangs\BidangResource;
use App\Services\BidangService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditBidang extends EditRecord
{
    protected static string $resource = BidangResource::class;

    private BidangService $bidangService;

    public function __construct()
    {
        parent::__construct();
        $this->bidangService = new BidangService();
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Sanitize and validate data using BidangService
        $sanitized = $this->bidangService->sanitize($data);
        
        $record->update($sanitized);

        return $record->fresh();
    }
}
