<?php

namespace App\Filament\Resources\Agendas\Pages;

use App\Filament\Resources\Agendas\AgendaResource;
use Carbon\Carbon;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAgendas extends ListRecords
{
    protected static string $resource = AgendaResource::class;

    public function getTabs(): array
    {
        $now = Carbon::now('Asia/Jakarta');

        return [
            'semua' => Tab::make('Semua')
                ->icon('heroicon-m-calendar'),

            'akan_datang' => Tab::make('Akan Datang')
                ->icon('heroicon-m-arrow-right-circle')
                ->badge(
                    \App\Models\Agenda::query()
                        ->where('start_date', '>', $now)
                        ->count()
                )
                ->badgeColor('info')
                ->modifyQueryUsing(
                    fn(Builder $query) => $query->where('start_date', '>', $now)
                ),

            'berlangsung' => Tab::make('Sedang Berlangsung')
                ->icon('heroicon-m-play-circle')
                ->badge(
                    \App\Models\Agenda::query()
                        ->where('start_date', '<=', $now)
                        ->where(function ($q) use ($now) {
                            $q->where('end_date', '>=', $now)
                                ->orWhere(function ($q2) use ($now) {
                                    $q2->whereNull('end_date')
                                        ->whereDate('start_date', $now->toDateString());
                                });
                        })
                        ->count()
                )
                ->badgeColor('success')
                ->modifyQueryUsing(
                    fn(Builder $query) => $query
                        ->where('start_date', '<=', $now)
                        ->where(function ($q) use ($now) {
                            $q->where('end_date', '>=', $now)
                                ->orWhere(function ($q2) use ($now) {
                                    $q2->whereNull('end_date')
                                        ->whereDate('start_date', $now->toDateString());
                                });
                        })
                ),

            'selesai' => Tab::make('Selesai')
                ->icon('heroicon-m-check-circle')
                ->badge(
                    \App\Models\Agenda::query()
                        ->where(function ($q) use ($now) {
                            $q->where('end_date', '<', $now)
                                ->orWhere(function ($q2) use ($now) {
                                    $q2->whereNull('end_date')
                                        ->whereDate('start_date', '<', $now->toDateString());
                                });
                        })
                        ->count()
                )
                ->badgeColor('gray')
                ->modifyQueryUsing(
                    fn(Builder $query) => $query
                        ->where(function ($q) use ($now) {
                            $q->where('end_date', '<', $now)
                                ->orWhere(function ($q2) use ($now) {
                                    $q2->whereNull('end_date')
                                        ->whereDate('start_date', '<', $now->toDateString());
                                });
                        })
                ),
        ];
    }
}
