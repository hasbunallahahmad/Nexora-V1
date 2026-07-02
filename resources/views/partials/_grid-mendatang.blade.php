@if ($mendatang->isEmpty())
    <div class="col-span-full text-center py-10" style="color:#e9ecf0;" role="status" aria-live="polite">
        <p class="text-4xl mb-3 opacity-30" aria-hidden="true">🗓️</p>
        <p>Belum ada agenda mendatang yang terdaftar.</p>
    </div>
@else
    <div class="grid gap-4 sm:gap-5" style="grid-template-columns:repeat(auto-fill,minmax(min(340px,100%),1fr));"
        role="list" aria-label="Daftar agenda mendatang">
        @foreach ($mendatang as $agenda)
            @include('partials.agenda-card', ['agenda' => $agenda])
        @endforeach
    </div>
@endif
