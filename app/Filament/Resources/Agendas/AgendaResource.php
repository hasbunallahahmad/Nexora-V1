<?php

namespace App\Filament\Resources\Agendas;

use App\Activity\Models\Agenda;
use App\Filament\Resources\Agendas\Pages\ListAgendas;
use App\Filament\Resources\Agendas\Schemas\AgendaForm;
use App\Filament\Resources\Agendas\Tables\AgendasTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AgendaResource extends Resource
{
    protected static ?string $model = Agenda::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CalendarDays;

    protected static ?string $navigationLabel = 'Agenda';

    protected static ?string $pluralLabel = 'Agenda';

    protected static ?string $slug = 'Agenda';

    protected static ?string $recordTitleAttribute = 'agenda';

    public static function form(Schema $schema): Schema
    {
        return AgendaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AgendasTable::configure($table);
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
            'index' => ListAgendas::route('/'),
            // 'create' => CreateAgenda::route('/create'),
            // 'edit' => EditAgenda::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteKeyName(): ?string
    {
        return 'slug';
    }
}
