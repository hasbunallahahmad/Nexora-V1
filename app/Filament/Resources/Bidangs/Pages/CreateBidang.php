<?php

namespace App\Filament\Resources\Bidangs\Pages;

use App\Filament\Resources\Bidangs\BidangResource;
use App\Services\BidangService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateBidang extends CreateRecord
{
    protected static string $resource = BidangResource::class;

    private BidangService $bidangService;

    public function __construct()
    {
        parent::__construct();
        $this->bidangService = new BidangService();
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Sanitize and validate data using BidangService
        $sanitized = $this->bidangService->sanitize($data);
        
        return $this->getModel()::create($sanitized);
    }
}
