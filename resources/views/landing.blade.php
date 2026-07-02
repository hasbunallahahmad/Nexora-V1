@extends('layouts.app')

@section('title', e(config('app.name')))

@section('config')
    <script>
        window.AgendaConfig = Object.freeze(@json([
            'kalenderUrl' => url('/api/agenda/kalender'),
        ]));
    </script>
@endsection

@section('content')
    <section class="relative overflow-hidden" aria-label="Selamat datang di Agenda Arpusda"
        style="background:linear-gradient(135deg,var(--navy) 0%,var(--navy-light) 60%,#1a4a7a 100%);min-height:480px;">
        <div aria-hidden="true" style="position:absolute;inset:0;pointer-events:none;overflow:hidden;">
            <div
                style="position:absolute;top:-80px;right:-80px;width:400px;height:400px;border-radius:50%;background:rgba(201,168,76,0.05);">
            </div>
            <div
                style="position:absolute;bottom:-60px;left:-60px;width:300px;height:300px;border-radius:50%;background:rgba(201,168,76,0.04);">
            </div>
        </div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 py-12 sm:py-16 w-full">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12 items-center">

                <div>
                    <h2 class="text-white font-black leading-tight mb-4"
                        style="font-family:'Playfair Display',serif;font-size:clamp(1.8rem,4vw,2.8rem);min-height:6rem;">
                        Informasi <span style="color:var(--gold-light)">Agenda</span><br>Kegiatan Arpusda
                    </h2>
                    <p class="text-sm sm:text-base leading-relaxed mb-6 sm:mb-8" style="color:rgba(255,255,255,0.6);">
                        Portal resmi jadwal kegiatan dan agenda dinas. Transparansi informasi publik
                        secara real-time untuk seluruh pegawai dan masyarakat.
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <a href="#agenda-hari-ini"
                            class="px-5 sm:px-7 py-3 rounded-lg font-bold text-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-lg active:scale-95 focus-visible:ring-2 focus-visible:ring-yellow-400 focus-visible:ring-offset-1"
                            style="background:var(--gold);color:var(--navy);box-shadow:0 2px 0 rgba(0,0,0,0.2);">
                            Lihat Agenda Hari Ini
                        </a>
                        <a href="#kalender"
                            class="px-5 sm:px-7 py-3 rounded-lg font-medium text-sm border text-white transition-all duration-200 hover:-translate-y-1 hover:bg-white/10 active:scale-95 focus-visible:ring-2 focus-visible:ring-yellow-400 focus-visible:ring-offset-1"
                            style="border-color:rgba(255,255,255,0.3);">
                            Buka Kalender
                        </a>
                    </div>
                </div>


                <div class="flex flex-col gap-3">
                    <div class="rounded-2xl p-5 sm:p-6 text-center border"
                        style="background:rgba(255,255,255,0.06);border-color:rgba(255,255,255,0.12);min-height:100px;"
                        aria-live="polite" aria-atomic="true" aria-label="Waktu saat ini">
                        <p class="font-bold mb-1"
                            style="font-family:'Playfair Display',serif;color:var(--gold-light);font-size:clamp(2rem,5vw,3rem);min-height:3.2rem;line-height:1.1;"
                            id="liveClock" aria-hidden="true">--:--:--</p>
                        <p class="text-xs sm:text-sm" style="color:rgba(255,255,255,0.5);" id="liveDate">Memuat...</p>
                    </div>

                    <div class="grid grid-cols-3 gap-2" role="list" aria-label="Statistik agenda">
                        @foreach ([['hari_ini', 'Hari Ini'], ['mendatang', 'Mendatang'], ['minggu_ini', 'Minggu Ini']] as [$key, $label])
                            <div class="rounded-xl p-3 sm:p-4 text-center border"
                                style="background:rgba(255,255,255,0.07);border-color:rgba(255,255,255,0.1);min-height:80px;"
                                role="listitem">
                                {{-- <p class="font-bold" aria-label="{{ (int) ($stats[$key] ?? 0) }} agenda {{ $label }}"
                                    style="font-family:'Playfair Display',serif;color:var(--gold-light);font-size:clamp(1.5rem,3vw,1.875rem);min-height:2.4rem;">
                                    {{ (int) ($stats[$key] ?? 0) }}
                                </p> --}}
                                <p class="font-bold" aria-label="{{ (int) ($stats[$key] ?? 0) }} agenda {{ $label }}"
                                    style="font-family:'Playfair Display',serif;color:var(--gold-light);font-size:clamp(1.5rem,3vw,1.875rem);min-height:2.4rem;"
                                    data-stat="{{ $key }}">
                                    {{ (int) ($stats[$key] ?? 0) }}
                                </p>
                                <p class="text-xs mt-1" style="color:rgba(255,255,255,0.5);">{{ $label }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section id="agenda-hari-ini" class="py-12 sm:py-16 bg-white" style="min-height:320px;"
        aria-labelledby="heading-hari-ini">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">

            <p class="text-xs font-bold tracking-widest uppercase mb-1" style="color:var(--gold);">📅 Jadwal Terkini</p>
            <h2 id="heading-hari-ini" class="font-bold mb-1"
                style="font-family:'Playfair Display',serif;color:var(--navy);font-size:clamp(1.5rem,3vw,1.875rem);">
                Agenda <span style="color:var(--gold)">Hari Ini</span>
            </h2>
            <div class="w-12 h-1 rounded my-3" style="background:var(--gold);" role="presentation"></div>
            <p class="text-sm mb-6 sm:mb-8" style="color:#5a6a82;">
                Jadwal kegiatan untuk
                <strong>{{ now()->translatedFormat('l, d F Y') }}</strong>
            </p>

            {{-- @if ($hariIni->isEmpty())
                <div class="text-center py-10 sm:py-14 rounded-2xl border-2 border-dashed"
                    style="background:var(--cream);border-color:var(--cream-dark);" role="status" aria-live="polite">
                    <p class="text-4xl sm:text-5xl mb-3 opacity-40" aria-hidden="true">📋</p>
                    <h3 class="font-bold text-lg sm:text-xl mb-2"
                        style="font-family:'Playfair Display',serif;color:var(--navy);">
                        Tidak Ada Agenda Hari Ini
                    </h3>
                    <p style="color:#5a6a82;">Tidak ada jadwal kegiatan yang terdaftar untuk hari ini.</p>
                </div>
            @else
                <div class="grid gap-4 sm:gap-5"
                    style="grid-template-columns:repeat(auto-fill,minmax(min(340px,100%),1fr));" role="list"
                    aria-label="Daftar agenda hari ini">
                    @foreach ($hariIni as $agenda)
                        @include('partials.agenda-card', ['agenda' => $agenda])
                    @endforeach
                </div>
            @endif --}}
            <div id="grid-hari-ini">
                @include('partials._grid-hari-ini')
            </div>
        </div>
    </section>

    <section id="agenda-mendatang" class="py-12 sm:py-16" style="background:var(--cream);min-height:320px;"
        aria-labelledby="heading-mendatang">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">

            <p class="text-xs font-bold tracking-widest uppercase mb-1" style="color:var(--gold);">🗓️ Jadwal Ke Depan</p>
            <h2 id="heading-mendatang" class="font-bold mb-1"
                style="font-family:'Playfair Display',serif;color:var(--navy);font-size:clamp(1.5rem,3vw,1.875rem);">
                Agenda <span style="color:var(--gold)">Mendatang</span>
            </h2>
            <div class="w-12 h-1 rounded my-3" style="background:var(--gold);" role="presentation"></div>
            <p class="text-sm mb-6 sm:mb-8" style="color:#5a6a82;">Klik kartu untuk melihat detail lengkap kegiatan.</p>

            {{-- <div class="grid gap-4 sm:gap-5" style="grid-template-columns:repeat(auto-fill,minmax(min(340px,100%),1fr));"
                role="list" aria-label="Daftar agenda mendatang">
                {{-- @forelse ($mendatang as $agenda)
                    @include('partials.agenda-card', ['agenda' => $agenda])
                @empty
                    <div class="col-span-full text-center py-10" style="color:#5a6a82;" role="status" aria-live="polite">
                        <p class="text-4xl mb-3 opacity-30" aria-hidden="true">🗓️</p>
                        <p>Belum ada agenda mendatang yang terdaftar.</p>
                    </div>
                @endforelse --}}
            {{-- </div> --}}
            <div id="grid-mendatang">
                @include('partials._grid-mendatang')
            </div>
        </div>
    </section>

    <section id="kalender" class="py-12 sm:py-16 relative overflow-hidden" style="background:var(--navy);min-height:850px;"
        aria-labelledby="heading-kalender">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 relative z-10">

            <p class="text-xs font-bold tracking-widest uppercase mb-1" style="color:var(--gold-light);">📆 Tampilan
                Kalender</p>
            <h2 id="heading-kalender" class="font-bold mb-1 text-white"
                style="font-family:'Playfair Display',serif;font-size:clamp(1.5rem,3vw,1.875rem);">
                Kalender <span style="color:var(--gold)">Event</span>
            </h2>
            <div class="w-12 h-1 rounded my-3" style="background:var(--gold);" role="presentation"></div>
            <p class="text-sm mb-6 sm:mb-8" style="color:rgba(255,255,255,0.5);">
                Klik event untuk melihat detail kegiatan.
            </p>

            <div class="rounded-xl sm:rounded-2xl p-4 sm:p-6 border"
                style="background:rgba(255,255,255,0.04);border-color:rgba(255,255,255,0.1);min-height:600px;">
                <div id="calendar" role="application" aria-label="Kalender agenda kegiatan"></div>
            </div>
        </div>
    </section>
    @push('scripts')
        <style>
            @keyframes agendaToastIn {
                from {
                    opacity: 0;
                    transform: translateY(12px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes agendaToastOut {
                from {
                    opacity: 1;
                    transform: translateY(0);
                }

                to {
                    opacity: 0;
                    transform: translateY(12px);
                }
            }
        </style>
        {{-- <script>
            (function() {
                const POLL_INTERVAL = 5000; // 10 detik

                // Snapshot awal dari SSR — tidak perlu fetch pertama kali
                let cachedStats = @json($stats);
                let cachedLastModified = @json($lastModified ?? null);

                async function pollAgenda() {
                    try {
                        const res = await fetch('{{ route('api.agenda.polling') }}', {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (!res.ok) return;

                        const data = await res.json();

                        // Cek apakah ada perubahan data
                        const changed = detectChange(data.stats, data.last_modified);

                        // Update DOM hanya jika ada perubahan
                        if (changed) {
                            updateStats(data.stats);
                            document.getElementById('grid-hari-ini').innerHTML = data.hari_ini_html;
                            document.getElementById('grid-mendatang').innerHTML = data.mendatang_html;
                            cachedStats = data.stats;
                            cachedLastModified = data.last_modified;
                            showToast('Agenda telah diperbarui');
                        }

                    } catch (_) {}
                }

                function detectChange(newStats, newLastModified) {
                    const statsChanged = ['hari_ini', 'mendatang', 'minggu_ini'].some(
                        key => newStats[key] !== cachedStats[key]
                    );
                    const contentChanged = cachedLastModified !== null &&
                        newLastModified !== cachedLastModified;

                    return statsChanged || contentChanged;
                }

                function updateStats(newStats) {
                    ['hari_ini', 'mendatang', 'minggu_ini'].forEach(key => {
                        const el = document.querySelector(`[data-stat="${key}"]`);
                        if (el) el.textContent = newStats[key];
                    });
                }

                function showToast(msg) {
                    const t = document.createElement('div');
                    t.textContent = '🔄 ' + msg;
                    Object.assign(t.style, {
                        position: 'fixed',
                        bottom: '24px',
                        right: '24px',
                        zIndex: '9999',
                        background: 'var(--navy)',
                        color: 'white',
                        padding: '12px 20px',
                        borderRadius: '12px',
                        fontSize: '0.875rem',
                        fontWeight: '600',
                        boxShadow: '0 4px 20px rgba(0,0,0,0.25)',
                        borderLeft: '4px solid var(--gold)',
                        animation: 'agendaToastIn .3s ease',
                    });
                    document.body.appendChild(t);
                    setTimeout(() => {
                        t.style.animation = 'agendaToastOut .3s ease forwards';
                        setTimeout(() => t.remove(), 300);
                    }, 3500);
                }

                // Mulai polling setelah halaman siap
                setInterval(pollAgenda, POLL_INTERVAL);
            })();
        </script> --}}
        <script>
            (function() {
                const POLL_INTERVAL = 30000; // 30 detik
                let calendarInstance = null;

                // Store calendar reference saat di-render
                window.storeCalendarInstance = function(cal) {
                    calendarInstance = cal;
                };

                async function pollAgenda() {
                    try {
                        const res = await fetch('{{ route('api.agenda.polling') }}', {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (!res.ok) return;

                        const data = await res.json();

                        // Selalu update DOM setiap poll
                        document.getElementById('grid-hari-ini').innerHTML = data.hari_ini_html;
                        document.getElementById('grid-mendatang').innerHTML = data.mendatang_html;

                        ['hari_ini', 'mendatang', 'minggu_ini'].forEach(key => {
                            const el = document.querySelector(`[data-stat="${key}"]`);
                            if (el) el.textContent = data.stats[key];
                        });

                        // Refresh kalender dengan version baru untuk invalidate cache
                        if (calendarInstance && window.location.pathname === '/') {
                            setTimeout(() => {
                                calendarInstance.refetchEvents();
                            }, 500);
                        }

                    } catch (_) {}
                }

                setInterval(pollAgenda, POLL_INTERVAL);
            })();
        </script>
    @endpush
@endsection
