<?php

namespace App\Filament\Resources\Agendas\Tables;

use App\Activity\Models\Agenda;
use App\Activity\Services\AgendaService;
use App\Filament\Exports\AgendaExport;
use App\Filament\Resources\Agendas\Schemas\AgendaForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;

class AgendasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->query(Agenda::query()->with('bidang'))
            ->striped()
            ->defaultSort('created_at', 'desc')
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(4)
            ->columns([
                TextColumn::make('judul_agenda')
                    ->label('Judul Agenda')
                    ->searchable()
                    ->wrap()
                    ->limit(50)
                    ->tooltip(fn($state) => $state)
                    ->grow(false)
                    ->sortable(),
                TextColumn::make('deskripsi')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->grow(false)
                    ->tooltip(fn($state) => $state)
                    ->wrap(),
                TextColumn::make('location')
                    ->label('Lokasi')
                    ->searchable()
                    ->tooltip(fn($state) => $state)
                    ->grow(false)
                    ->wrap()
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label('Tanggal Mulai')
                    ->dateTime('d M Y H:i')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Tanggal Selesai')
                    ->dateTime('d M Y H:i')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('bidang.nama_bidang')
                    ->label('Disposisi Ke')
                    ->searchable()
                    ->wrap()
                    ->tooltip(fn($record) => $record->bidang->pluck('nama_bidang')->join(','))
                    ->grow(false)
                    ->sortable(),
                IconColumn::make('is_published')
                    ->label('Publikasi')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('dari')
                    ->label('Dari Tanggal')
                    ->schema([
                        DatePicker::make('dari')
                            ->label('Dari Tanggal')
                            ->placeholder('Tanggal awal')
                            ->native(false),
                    ])
                    ->query(
                        fn(Builder $query, array $data): Builder =>
                        $query->when(
                            $data['dari'],
                            fn(Builder $q, $date) => $q->whereDate('start_date', '>=', $date)
                        )
                    )
                    ->indicateUsing(
                        fn(array $data): ?string =>
                        $data['dari']
                            ? 'Dari: ' . \Carbon\Carbon::parse($data['dari'])->translatedFormat('d M Y')
                            : null
                    ),

                Filter::make('sampai')
                    ->label('Sampai Tanggal')
                    ->schema([
                        DatePicker::make('sampai')
                            ->label('Sampai Tanggal')
                            ->placeholder('Tanggal akhir')
                            ->native(false),
                    ])
                    ->query(
                        fn(Builder $query, array $data): Builder =>
                        $query->when(
                            $data['sampai'],
                            fn(Builder $q, $date) => $q->whereDate('start_date', '<=', $date)
                        )
                    )
                    ->indicateUsing(
                        fn(array $data): ?string =>
                        $data['sampai']
                            ? 'Sampai: ' . \Carbon\Carbon::parse($data['sampai'])->translatedFormat('d M Y')
                            : null
                    ),

                SelectFilter::make('bidang')
                    ->label('Bidang')
                    ->relationship('bidang', 'nama_bidang', fn($query) => $query->orderBy('id'))
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->placeholder('Semua Bidang'),

                SelectFilter::make('month')
                    ->label('Bulan')
                    ->options([
                        '01' => 'Januari',
                        '02' => 'Februari',
                        '03' => 'Maret',
                        '04' => 'April',
                        '05' => 'Mei',
                        '06' => 'Juni',
                        '07' => 'Juli',
                        '08' => 'Agustus',
                        '09' => 'September',
                        '10' => 'Oktober',
                        '11' => 'November',
                        '12' => 'Desember',
                    ])
                    ->query(
                        fn(Builder $query, array $data): Builder =>
                        $query->when(
                            $data['value'],
                            fn(Builder $q, $month) => $q->whereMonth('start_date', $month)
                        )
                    )
                    ->placeholder('Pilih Bulan'),
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->modalHeading('Edit Agenda')
                    ->modalWidth('lg')
                    ->schema(AgendaForm::getComponents())
                    ->using(function (Agenda $record, array $data, AgendaService $service): Agenda {
                        return $service->update($record->id, $data);
                    }),
                DeleteAction::make()
                    ->iconButton(),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label('Tambah Agenda')
                    ->modalHeading('Buat Agenda Baru')
                    ->modalSubmitActionLabel('Simpan')
                    ->modalWidth('3xl')
                    ->schema(AgendaForm::getComponents())
                    ->using(function (array $data, AgendaService $service): Agenda {
                        return $service->create($data);
                    }),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ExportAction::make()->exports([
                        AgendaExport::make('agenda')
                            ->askForFilename()
                            ->askForWriterType()
                            ->modifyQueryUsing(function ($query, $livewire) {
                                $bulan = data_get($livewire->tableFilters, 'month.value');
                                AgendaExport::$selectedBulan = $bulan ?: null;
                                if ($bulan) {
                                    $query->whereMonth('start_date', $bulan);
                                }
                                $dari = data_get($livewire->tableFilters, 'dari.dari');
                                if ($dari) {
                                    $query->whereDate('start_date', '>=', $dari);
                                }

                                $sampai = data_get($livewire->tableFilters, 'sampai.sampai');
                                if ($sampai) {
                                    $query->whereDate('start_date', '<=', $sampai);
                                }

                                return $query;
                            })
                            ->withColumns([
                                Column::make('judul_agenda')
                                    ->heading('Judul Agenda')
                                    ->width(25),
                                Column::make('deskripsi')
                                    ->heading('Deskripsi')
                                    ->width(25),
                                Column::make('location')
                                    ->heading('Lokasi'),
                                Column::make('start_date')
                                    ->heading('Tanggal Mulai')
                                    ->formatStateUsing(fn($state) => $state ? $state->format('d/m/Y H:i') : '-'),
                                Column::make('end_date')
                                    ->heading('Tanggal Selesai')
                                    ->formatStateUsing(fn($state) => $state ? $state->format('d/m/Y H:i') : '-'),
                                Column::make('bidang')
                                    ->heading('Disposisi Ke')
                                    ->getStateUsing(
                                        fn($record) => $record->bidang->pluck('nama_bidang')->join(','),
                                    ),
                            ])
                    ]),
                ]),
            ]);
    }
}
