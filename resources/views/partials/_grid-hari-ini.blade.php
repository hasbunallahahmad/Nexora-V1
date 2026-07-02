@if ($hariIni->isEmpty())
    <div class="text-center py-10 sm:py-14 rounded-2xl border-2 border-dashed"
        style="background:var(--cream);border-color:var(--cream-dark);" role="status" aria-live="polite">
        <p class="text-4xl sm:text-5xl mb-3 opacity-40" aria-hidden="true">📋</p>
        <h3 class="font-bold text-lg sm:text-xl mb-2" style="font-family:'Playfair Display',serif;color:var(--navy);">
            Tidak Ada Agenda Hari Ini
        </h3>
        <p style="color:#5a6a82;">Tidak ada jadwal kegiatan yang terdaftar untuk hari ini.</p>
    </div>
@else
    <div class="grid gap-4 sm:gap-5" style="grid-template-columns:repeat(auto-fill,minmax(min(340px,100%),1fr));"
        role="list" aria-label="Daftar agenda hari ini">
        @foreach ($hariIni as $agenda)
            @include('partials.agenda-card', ['agenda' => $agenda])
        @endforeach
    </div>
@endif
