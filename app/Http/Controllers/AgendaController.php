<?php

namespace App\Http\Controllers;

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
    public function index()
    {
        $hariIni = Agenda::published()
            ->hariIni()
            ->with('bidang')
            ->get();

        $mendatang = Agenda::published()
            ->mendatang()
            ->with('bidang')
            ->take(9)
            ->get();

        $stats = Cache::remember('agenda_stats', 60, fn() => [
            'hari_ini'   => Agenda::published()->hariIni()->count('*'),
            'mendatang'  => Agenda::published()->mendatang()->count('*'),
            'minggu_ini' => Agenda::published()->mingguIni()->count('*'),
        ]);

        $lastModified = Agenda::published()
            ->whereIn('id', $hariIni->pluck('id')->merge($mendatang->pluck('id')))
            ->max('updated_at');

        return view('landing', compact('hariIni', 'mendatang', 'stats'));
    }


    public function polling(): JsonResponse
    {
        $hariIni   = Agenda::published()->hariIni()->with('bidang')->get();
        $mendatang = Agenda::published()->mendatang()->with('bidang')->take(9)->get();

        // Clear cache untuk invalidate kalender
        Cache::forget('agenda_stats');

        // Increment cache version untuk force kalender reload
        // Ini membuat cache key kalender menjadi invalid, sehingga API akan fetch data baru
        $currentVersion = Cache::get('kalender_cache_version', 0);
        Cache::put('kalender_cache_version', $currentVersion + 1, now()->addHours(24));

        $stats = [
            'hari_ini'   => Agenda::published()->hariIni()->count('*'),
            'mendatang'  => Agenda::published()->mendatang()->count('*'),
            'minggu_ini' => Agenda::published()->mingguIni()->count('*'),
        ];

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

        $version  = Cache::get('kalender_cache_version', 0);
        $cacheKey = 'kalender_'
            . $start->toDateString() . '_'
            . $end->toDateString()
            . '_v' . $version;

        Log::info('Kalender Feed Request', [
            'start'     => $start->toDateTimeString(),
            'end'       => $end->toDateTimeString(),
        ]);
        $events = Cache::remember($cacheKey, now()->addHours(24), function () use ($start, $end) {
            return Agenda::published()
                ->betweenDates($start->toDateString(), $end->toDateString())
                ->with('bidang')
                ->get()
                ->map(fn(Agenda $a) => AgendaCalendarResource::make($a)->resolve())
                ->values();
        });

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

        $agenda = Agenda::published()
            ->with('bidang')
            ->findOrFail($id);

        return response()
            ->json(AgendaDetailResource::make($agenda))
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('Cache-Control', 'no-store, private');
    }
}
