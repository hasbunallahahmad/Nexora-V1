<?php

namespace App\Observers;

use App\Calendar\Events\CalendarSourceChanged;
use App\Models\Agenda;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AgendaObserver
{
    public function created(Agenda $agenda): void
    {
        $this->syncCalendar($agenda);
    }

    public function updated(Agenda $agenda): void
    {
        $this->syncCalendar($agenda);
    }

    public function deleted(Agenda $agenda): void
    {
        $this->syncCalendar($agenda);
    }

    public function restored(Agenda $agenda): void
    {
        $this->syncCalendar($agenda);
    }

    public function forceDeleted(Agenda $agenda): void
    {
        $this->syncCalendar($agenda);
    }

    private function syncCalendar(Agenda $agenda): void
    {
        Cache::forget('agenda_stats');

        event(new CalendarSourceChanged('agenda'));
    }

    private function clearCache(Agenda $agenda): void
    {
        Cache::forget('agenda_stats');

        $current = Cache::get('kalender_cache_version', 0);
        Cache::put('kalender_cache_version', $current + 1, now()->addDays(90));

        if ($agenda->start_date) {
            for ($i = -1; $i <= 1; $i++) {
                $date = $agenda->start_date->copy()->addMonths($i);
                $start = $date->copy()->startOfMonth()->toDateString();
                $end = $date->copy()->endOfMonth()->toDateString();
                Cache::forget('kalender_' . $start . '_' . $end);
            }
        }
    }
}
