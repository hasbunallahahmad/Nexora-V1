<?php

namespace App\Filament\Resources\Bidangs;

use App\Filament\Resources\Bidangs\Pages\ListBidangs;
use App\Filament\Resources\Bidangs\Schemas\BidangForm;
use App\Filament\Resources\Bidangs\Tables\BidangsTable;
use App\Models\Bidang;
use App\Services\BidangService;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;



class BidangResource extends Resource
{
    protected static ?string $model = Bidang::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserPlus;

    protected static ?string $navigationLabel = 'Bidang';

    protected static ?string $pluralLabel = 'Bidang';

    protected static ?string $slug = 'bidang';

    protected static ?string $recordTitleAttribute = 'nama_bidang';

    protected BidangService $bidangService;

    public function __construct()
    {
        $this->bidangService = app(BidangService::class);
    }

    public static function form(Schema $schema): Schema
    {
        return BidangForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BidangsTable::configure($table);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        return app(BidangService::class)->sanitize($data);
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        return app(BidangService::class)->sanitize($data);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBidangs::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withTrashed();
    }

    public static function getRecordRouteKeyName(): ?string
    {
        return 'slug';
    }
}
