<?php

namespace App\Http\Controllers;

use App\Calendar\DTO\CalendarQuery;
use App\Calendar\Enums\CalendarAudience;
use App\Calendar\Services\CalendarAggregationService;
use App\Http\Requests\KalenderFeedRequest;
use App\Http\Resources\AgendaCalendarResource;
use App\Http\Resources\AgendaDetailResource;
use App\Models\Agenda;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;


class AgendaController extends Controller
{
    public function __construct(
        private readonly CalendarAggregationService $calendar,
    ) {}

    public function index()
    {
        $hariIni = Agenda::published()->hariIni()->with('bidang')->get();
        $mendatang = Agenda::published()->mendatang()->with('bidang')->take(9)->get();

        $stats = Cache::remember('agenda_stats', 60, fn() => [
            'hari_ini'   => Agenda::published()->hariIni()->count('*'),
            'mendatang'  => Agenda::published()->mendatang()->count('*'),
            'minggu_ini' => Agenda::published()->mingguIni()->count('*'),
        ]);

        return view('landing', compact('hariIni', 'mendatang', 'stats'));
    }


    public function polling(): JsonResponse
    {
        $hariIni   = Agenda::published()->hariIni()->with('bidang')->get();
        $mendatang = Agenda::published()->mendatang()->with('bidang')->take(9)->get();

        $stats = Cache::remember('agenda_stats', 60, fn() => [
            'hari_ini'   => Agenda::published()->hariIni()->count('*'),
            'mendatang'  => Agenda::published()->mendatang()->count('*'),
            'minggu_ini' => Agenda::published()->mingguIni()->count('*'),
        ]);

        return response()->json([
            'stats'          => $stats,
            'hari_ini_html'  => View::make('partials._grid-hari-ini', compact('hariIni'))->render(),
            'mendatang_html' => View::make('partials._grid-mendatang', compact('mendatang'))->render(),
        ])->header('Cache-Control', 'no-store, private');
    }
    // ══════════════════════════════════════════════════════════════════
    // API: KALENDER FEED (untuk FullCalendar)
    // GET /api/agenda/kalender?start=2026-03-01&end=2026-03-31
    // ══════════════════════════════════════════════════════════════════

    public function kalenderFeed(KalenderFeedRequest $request): JsonResponse
    {
        ['start' => $start, 'end' => $end] = $request->parsed();

        Log::info('Kalender Feed Request', [
            'start' => $start->toDateTimeString(),
            'end'   => $end->toDateTimeString(),
        ]);

        $query = new CalendarQuery($start, $end, CalendarAudience::Public);

        $events = $this->calendar->getEvents($query)->map(fn($event) => [
            'id'              => $event->id,
            'title'           => $event->title,
            'start'           => $event->start->toDateString(),
            'end'             => $event->end?->copy()->addDay()->toDateString(),
            'backgroundColor' => $event->backgroundColor,
            'borderColor'     => 'transparent',
            'textColor'       => '#ffffff',
            'extendedProps'   => $event->extendedProps,
        ])->values();

        return response()->json($events)
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('Cache-Control', 'no-store, private')
            ->header('X-Frame-Options', 'SAMEORIGIN');
    }

    // ══════════════════════════════════════════════════════════════════
    // API: DETAIL SATU AGENDA
    // GET /api/agenda/{id}
    // ══════════════════════════════════════════════════════════════════

    public function show(int $id): JsonResponse
    {
        if ($id <= 0) {
            return response()->json(['error' => 'ID tidak valid.'], 422);
        }

        $agenda = Agenda::published()->with('bidang')->findOrFail($id);

        return response()
            ->json(AgendaDetailResource::make($agenda))
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('Cache-Control', 'no-store, private');
    }
}
