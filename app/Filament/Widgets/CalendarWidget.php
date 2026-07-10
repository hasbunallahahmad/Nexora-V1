<?php

namespace App\Filament\Widgets;

use App\Calendar\DTO\CalendarQuery;
use App\Calendar\Enums\CalendarAudience;
use App\Calendar\Services\CalendarAggregationService;
use App\Filament\Resources\Agendas\Schemas\AgendaForm;
use App\Models\Agenda;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Saade\FilamentFullCalendar\Actions;
use Saade\FilamentFullCalendar\Actions\CreateAction;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarWidget extends FullCalendarWidget
{
    public Model | string | null $model = Agenda::class;

    public function getFormSchema(): array
    {
        return AgendaForm::getComponents();
    }

    protected function headerActions(): array
    {
        return [
            CreateAction::make()
                ->slideOver()
                ->label('Buat Agenda Baru')
                ->modalWidth(Width::Large)
                ->visible(fn() => Auth::user()->can('create', $this->getModel()))
                ->mountUsing(
                    function ($form, array $arguments) {
                        $form->fill([
                            'start_date' => $arguments['start'] ?? null,
                            'end_date'   => $arguments['end'] ?? null,
                            'is_published' => false,
                            'bidang_id' => [],
                        ]);
                    }
                ),
        ];
    }

    protected function modalActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn() => Auth::user()->can('update', $this->getModel()))
                ->mountUsing(
                    function (Agenda $record, $form, array $arguments) {
                        $startDate = $arguments['event']['start'] ?? $record->start_date;
                        $endDate = $arguments['event']['end'] ?? $record->end_date;

                        $form->fill([
                            'judul_agenda' => $record->judul_agenda,
                            'deskripsi' => $record->deskripsi,
                            'start_date' => Carbon::parse($startDate)->setTimezone('Asia/Jakarta'),
                            'end_date' => $endDate
                                ? Carbon::parse($endDate)->setTimezone('Asia/Jakarta')
                                : null,
                            'location' => $record->location,
                            'is_published' => $record->is_published,
                            'bidang_id' => $record->bidang()->pluck('id')->toArray(),
                        ]);
                    }
                ),
            Actions\DeleteAction::make()
                ->visible(fn(Agenda $record) => Auth::user()->can('delete', $record)),
        ];
    }

    protected function viewAction(): Action
    {
        return Actions\ViewAction::make()
            ->schema([
                TextInput::make('judul_agenda')
                    ->label('Judul Agenda')
                    ->disabled(),
                Textarea::make('deskripsi')
                    ->label('Deskripsi')
                    ->disabled()
                    ->columnSpanFull(),
                DateTimePicker::make('start_date')
                    ->label('Tanggal Mulai')
                    ->disabled(),
                DateTimePicker::make('end_date')
                    ->label('Tanggal Selesai')
                    ->disabled(),
                TextInput::make('location')
                    ->label('Lokasi')
                    ->disabled(),
                Textarea::make('bidang')
                    ->label('Bidang')
                    ->disabled()
                    ->columnSpanFull(),
                \Filament\Forms\Components\Toggle::make('is_published')
                    ->label('Status Publikasi')
                    ->disabled(),
            ])
            ->modalHeading('Detail Agenda')
            ->mutateRecordDataUsing(function (Agenda $record): array {
                return [
                    'judul_agenda' => $record->judul_agenda,
                    'deskripsi' => $record->deskripsi,
                    'start_date' => Carbon::parse($record->start_date)->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s'),
                    'end_date' => $record->end_date
                        ? Carbon::parse($record->end_date)->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s')
                        : null,
                    'location' => $record->location,
                    'is_published' => $record->is_published,
                    'bidang' => $record->bidang()->pluck('nama_bidang')->implode(', '),
                ];
            });
    }

    public function fetchEvents(array $info): array
    {
        // return Agenda::query()
        //     ->where('start_date', '>=', $info['start'], 'and')
        //     ->where('start_date', '<=', $info['end'], 'and')
        //     ->get()
        //     ->map(
        //         fn(Agenda $event) => [
        //             'id' => $event->id,
        //             'title' => strip_tags($event->judul_agenda),
        //             'start' => $event->start_date,
        //             'end' => $event->end_date,
        //             'extendedProps' => [
        //                 'description' => strip_tags($event->deskripsi),
        //                 'location'    => strip_tags($event->location),
        //                 'bidang' => $event->bidang()->pluck('nama_bidang')->implode(', '),
        //                 'start_date' => Carbon::parse($event->start_date)->setTimezone('Asia/Jakarta')->format('Y-m-d H:i'),
        //                 'end_date'    => $event->end_date
        //                     ? Carbon::parse($event->end_date)->setTimezone('Asia/Jakarta')->format('Y-m-d H:i')
        //                     : null,
        //             ],
        //         ]
        //     )
        //     ->toArray();
        $query = new CalendarQuery(
            start: Carbon::parse($info['start']),
            end: Carbon::parse($info['end']),
            audience: CalendarAudience::Admin,
        );

        return app(CalendarAggregationService::class)
            ->getEvents($query)
            ->map(fn($event) => [
                'id'            => $event->id,
                'title'         => $event->title,
                'start'         => $event->start,
                'end'           => $event->end,
                'extendedProps' => $event->extendedProps,
            ])
            ->toArray();
    }

    public function config(): array
    {
        return [
            'firstDay' => 1,
            'headerToolbar' => [
                'left' => 'prev,next',
                'center' => 'title',
                'right' => 'dayGridMonth,timeGridWeek,timeGridDay'
            ],
            'initialView' => 'dayGridMonth',
            'height' => 'auto',
            'locale' => 'id',
            'buttonText' => [
                'today' => 'Hari Ini',
                'month' => 'Bulan',
                'week' => 'Minggu',
                'day' => 'Hari'
            ],
            'dayHeaderFormat' => [
                'weekday' => 'short'
            ],
            'moreLinkClick' => 'popover',
            'moreLinkContent' => 'popover',
            'fixedWeekCount' => false,
            'showNonCurrentDates' => true,
            'eventMouseEnter' => true,
            'eventMouseLeave' => true,
            'editable'    => true,
            'droppable'   => true,
            'businessHours' => [
                'daysOfWeek' => [1, 2, 3, 4, 5],
                'startTime' => '08:00',
                'endTime' => '17:00',
            ],
            'weekends' => true,
            'displayEventTime' => false,
            'eventDisplay'     => 'block',
            'eventTextColor' => '#ffffff',
            'timeZone' => 'Asia/Jakarta',
        ];
    }

    public function eventDidMount(): string
    {
        return <<<'JS'
        function({ event, el, view }) {

        function escHtml(str) {
            if (!str) return '-';
            const d = document.createElement('div');
            d.textContent = str;
            return d.innerHTML;
        }

        let tooltipContent = '<div style="background: rgba(17, 24, 39, 0.95); color: white; padding: 12px; border-radius: 8px; font-size: 13px; max-width: 280px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); z-index: 9999; line-height: 1.4;">';

        tooltipContent += '<div style="font-weight: 600; margin-bottom: 8px; color: #f3f4f6;">' + escHtml(event.title) + '</div>';

        if (event.extendedProps.start_date) {
            let startDate = new Date(event.extendedProps.start_date + '+07:00');
            let startTime = startDate.toLocaleString('id-ID', {
                weekday: 'long', year: 'numeric', month: 'long',
                day: 'numeric', hour: '2-digit', minute: '2-digit',
                timeZone: 'Asia/Jakarta'
            });
            tooltipContent += '<div style="margin-bottom: 6px; color: #e5e7eb;"><span style="color: #60a5fa;">📅</span> ' + escHtml(startTime) + '</div>';
        }

       if (event.extendedProps.end_date && event.extendedProps.end_date !== '' && event.extendedProps.end_date !== event.extendedProps.start_date) {
            let startDate = new Date(event.extendedProps.start_date + '+07:00');
            let endDate   = new Date(event.extendedProps.end_date + '+07:00');

            if (startDate.toDateString() !== endDate.toDateString()) {
                let endTime = endDate.toLocaleString('id-ID', {
                    weekday: 'long', year: 'numeric', month: 'long',
                    day: 'numeric', hour: '2-digit', minute: '2-digit',
                    timeZone: 'Asia/Jakarta'
                });
                tooltipContent += '<div style="margin-bottom: 6px; color: #e5e7eb;"><span style="color: #60a5fa;">🏁</span> ' + escHtml(endTime) + '</div>';
            } else {
                let endTime = endDate.toLocaleString('id-ID', {
                    hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Jakarta'
                });
                tooltipContent += '<div style="margin-bottom: 6px; color: #e5e7eb;"><span style="color: #60a5fa;">🕐</span> ' + escHtml(endTime) + '</div>';
            }
        }

        if (event.extendedProps.description) {
            tooltipContent += '<div style="margin-bottom: 6px; color: #e5e7eb;"><span style="color: #34d399;">📝</span> ' + escHtml(event.extendedProps.description) + '</div>';
        }
        if (event.extendedProps.location) {
            tooltipContent += '<div style="margin-bottom: 6px; color: #e5e7eb;"><span style="color: #f87171;">📍</span> ' + escHtml(event.extendedProps.location) + '</div>';
        }
        if (event.extendedProps.bidang) {
            tooltipContent += '<div style="color: #e5e7eb;"><span style="color: #fbbf24;">🏢</span> ' + escHtml(event.extendedProps.bidang) + '</div>';
        }

        tooltipContent += '</div>';
        el.style.cursor = 'pointer';
        el.style.borderRadius = '6px';
        el.style.transition = 'all 0.2s ease';

        let tooltip = null;
        let showTimeout = null;
        let hideTimeout = null;

        function showTooltip(e) {
            clearTimeout(hideTimeout);
            if (tooltip) tooltip.remove();

            showTimeout = setTimeout(() => {
                tooltip = document.createElement('div');
                tooltip.innerHTML = tooltipContent;
                tooltip.style.cssText = 'position:fixed;pointer-events:none;z-index:99999;opacity:0;transform:translateY(4px);transition:opacity 0.2s ease,transform 0.2s ease;';
                document.body.appendChild(tooltip);

                const rect        = el.getBoundingClientRect();
                const tooltipRect = tooltip.getBoundingClientRect();
                let left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
                let top  = rect.top - tooltipRect.height - 12;

                if (left < 8) left = 8;
                if (left + tooltipRect.width > window.innerWidth - 8) left = window.innerWidth - tooltipRect.width - 8;
                if (top < 8) top = rect.bottom + 12;

                tooltip.style.left = left + 'px';
                tooltip.style.top  = top  + 'px';

                requestAnimationFrame(() => {
                    tooltip.style.opacity   = '1';
                    tooltip.style.transform = 'translateY(0)';
                });
            }, 300);
        }

        function hideTooltip() {
            clearTimeout(showTimeout);
            hideTimeout = setTimeout(() => {
                if (tooltip) {
                    tooltip.style.opacity   = '0';
                    tooltip.style.transform = 'translateY(4px)';
                    setTimeout(() => { if (tooltip) { tooltip.remove(); tooltip = null; } }, 200);
                }
            }, 100);
        }

        el.addEventListener('mouseenter', function(e) {
            showTooltip(e);
            el.style.opacity   = '0.85';
            el.style.transform = 'scale(1.02)';
            el.style.boxShadow = '0 4px 12px rgba(0,0,0,0.1)';
        });

        el.addEventListener('mouseleave', function() {
            hideTooltip();
            el.style.opacity   = '1';
            el.style.transform = 'scale(1)';
            el.style.boxShadow = 'none';
        });

        ['scroll', 'resize'].forEach(type => window.addEventListener(type, hideTooltip, true));

        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.removedNodes.forEach((node) => {
                    if (node === el && tooltip) { tooltip.remove(); tooltip = null; }
                });
            });
        });
        observer.observe(document.body, { childList: true, subtree: true });
    }
    JS;
    }

    public function onEventClick(array $event): void
    {
        if ($this->getModel()) {
            $this->record = $this->resolveRecord($event['id']);
        }

        $this->mountAction('view', [
            'type' => 'click',
            'event' => $event,
        ]);
    }

    public function onEventDrop(array $event, array $oldEvent, array $relatedEvents, array $delta, ?array $oldResource, ?array $newResource): bool
    {
        try {
            $eventId = $event['id'] ?? null;

            if (!$eventId) {
                throw new \Exception('ID event tidak ditemukan');
            }

            $record = $this->resolveRecord($eventId);

            $startDate = Carbon::parse($event['start'])->setTimezone('Asia/Jakarta');
            $endDate = isset($event['end']) && $event['end']
                ? Carbon::parse($event['end'])->setTimezone('Asia/Jakarta')
                : null;

            $record->update([
                'start_date' => $startDate,
                'end_date'   => $endDate,
            ]);

            $this->refreshRecords();

            Notification::make()
                ->title('Jadwal berhasil dipindahkan')
                ->success()
                ->send();

            return true;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal memindahkan jadwal')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return false;
        }
    }

    public function onEventResize(array $event, array $oldEvent, array $relatedEvents, array $startDelta, array $endDelta): bool
    {
        try {
            $eventId = $event['id'] ?? null;

            if (!$eventId) {
                throw new \Exception('ID event tidak ditemukan');
            }

            $record = $this->resolveRecord($eventId);

            $startDate = Carbon::parse($event['start'])->setTimezone('Asia/Jakarta');
            $endDate = isset($event['end']) && $event['end']
                ? Carbon::parse($event['end'])->setTimezone('Asia/Jakarta')
                : null;

            $record->update([
                'start_date' => $startDate,
                'end_date'   => $endDate,
            ]);

            $this->refreshRecords();

            Notification::make()
                ->title('Durasi jadwal berhasil diubah')
                ->success()
                ->send();

            return true;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal mengubah durasi jadwal')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return false;
        }
    }

    public function resolveRecord(int|string $key): Model
    {
        $record = $this->getModel()::find($key);

        if (!$record) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException(
                "No query results for model [{$this->getModel()}] {$key}"
            );
        }

        return $record;
    }
}
