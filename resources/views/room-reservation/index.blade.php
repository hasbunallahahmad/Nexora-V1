@extends('layouts.app')

@section('title', 'Reservasi Ruangan — ' . e(config('app.name')))

@section('content')
    <section class="py-12 sm:py-16" style="background:var(--navy);min-height:100vh;">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">

            <p class="text-xs font-bold tracking-widest uppercase mb-1" style="color:var(--gold-light);">🏢 Layanan Fasilitas
            </p>
            <h1 class="font-bold mb-2 text-white"
                style="font-family:'Playfair Display',serif;font-size:clamp(1.5rem,3vw,2rem);">
                Reservasi <span style="color:var(--gold)">Ruangan</span>
            </h1>
            <p class="text-sm mb-8" style="color:rgba(255,255,255,0.6);">
                Ajukan reservasi ruangan untuk kegiatan Anda. Admin dinas akan menghubungi Anda via WhatsApp untuk
                konfirmasi.
            </p>

            @if (session('reservation_success'))
                @php
                    $sukses = session('reservation_success');
                    $pesanWa = "Halo, saya {$sukses['guest_name']} ingin konfirmasi reservasi ruangan {$sukses['room_name']} pada {$sukses['start_format']} WIB.";
                    $nomorAdmin = config('services.whatsapp.admin_number');
                @endphp
                <div class="rounded-xl p-4 mb-6 flex flex-col gap-3"
                    style="background:rgba(39,174,96,0.15);border:1px solid rgba(39,174,96,0.4);color:#a8e6c1;">
                    <p>✅ {{ $sukses['message'] }}</p>

                    @if ($nomorAdmin)
                        <a href="https://wa.me/{{ $nomorAdmin }}?text={{ urlencode($pesanWa) }}" target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 self-start px-4 py-2 rounded-lg font-semibold text-sm transition-all hover:-translate-y-0.5"
                            style="background:#25d366;color:#fff;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="white" aria-hidden="true">
                                <path
                                    d="M17.5 14.4c-.3-.1-1.7-.8-2-.9-.3-.1-.5-.1-.7.1-.2.3-.8.9-.9 1.1-.2.2-.3.2-.6.1s-1.2-.4-2.3-1.4c-.9-.8-1.5-1.7-1.6-2-.2-.3 0-.5.1-.6l.4-.5c.1-.2.2-.3.2-.5s-.1-.4-.2-.5C10.6 9 10 7.5 9.8 7c-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3C7.8 7.2 7 8 7 9.7c0 1.7 1.2 3.3 1.4 3.5.1.2 2.5 3.8 6 5.3.8.3 1.5.5 2 .7.8.2 1.6.2 2.2.1.7-.1 2-.8 2.3-1.6.3-.8.3-1.4.2-1.5-.1-.3-.3-.3-.6-.4z" />
                                <path
                                    d="M12 2C6.5 2 2 6.5 2 12c0 1.9.5 3.7 1.4 5.2L2 22l4.9-1.3C8.3 21.5 10.1 22 12 22c5.5 0 10-4.5 10-10S17.5 2 12 2zm0 18.2c-1.7 0-3.4-.5-4.8-1.3l-.3-.2-3 .8.8-2.9-.2-.3C3.7 15.1 3.2 13.6 3.2 12 3.2 7.1 7.1 3.2 12 3.2S20.8 7.1 20.8 12 16.9 20.2 12 20.2z" />
                            </svg>
                            Konfirmasi via WhatsApp →
                        </a>
                    @endif
                </div>
            @endif

            @if ($errors->has('conflict'))
                <div class="rounded-xl p-4 mb-6"
                    style="background:rgba(192,57,43,0.15);border:1px solid rgba(192,57,43,0.4);color:#f5b7b1;">
                    ⚠️ {{ $errors->first('conflict') }}
                </div>
            @endif

            {{-- Daftar Ruangan --}}
            <div class="grid gap-4 sm:grid-cols-2 mb-10">
                @foreach ($rooms as $room)
                    <div class="rounded-xl p-4"
                        style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);">
                        <p class="text-white font-semibold">{{ $room->name }}</p>
                        <p class="text-xs" style="color:rgba(255,255,255,0.5);">{{ $room->location ?? '—' }}</p>
                        <p class="text-xs mt-1" style="color:var(--gold-light);">Kapasitas maksimal: {{ $room->capacity }}
                            orang</p>
                    </div>
                @endforeach
            </div>

            {{-- Kalender Ketersediaan --}}
            <div class="rounded-xl p-4 sm:p-6 mb-10"
                style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);">
                <h2 class="text-white font-semibold mb-4">Ketersediaan Ruangan</h2>
                <div id="room-availability-calendar"></div>
            </div>

            {{-- Form Reservasi --}}
            <div class="rounded-xl p-4 sm:p-6"
                style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);">
                <h2 class="text-white font-semibold mb-4">Formulir Pengajuan</h2>

                <form id="form-reservasi-ruangan" method="POST" action="{{ route('room-reservation.store') }}"
                    class="flex flex-col gap-4">
                    @csrf

                    {{-- Honeypot — jangan diisi manusia --}}
                    <input type="text" name="website" value="" style="position:absolute;left:-9999px;"
                        tabindex="-1" autocomplete="off">

                    <div>
                        <label class="text-xs text-white mb-1 block">Ruangan</label>
                        <select name="room_id" required class="w-full rounded-lg p-2.5 text-sm"
                            style="background:rgba(255,255,255,0.9);">
                            <option value="">Pilih ruangan</option>
                            @foreach ($rooms as $room)
                                <option value="{{ $room->id }}" @selected(old('room_id') == $room->id)>
                                    {{ $room->name }} (maks. {{ $room->capacity }} orang)
                                </option>
                            @endforeach
                        </select>
                        @error('room_id')
                            <p class="text-xs mt-1" style="color:#f5b7b1;">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-xs text-white mb-1 block">Judul Keperluan</label>
                        <input type="text" name="title" value="{{ old('title') }}" required minlength="5"
                            maxlength="150" class="w-full rounded-lg p-2.5 text-sm"
                            style="background:rgba(255,255,255,0.9);">
                        @error('title')
                            <p class="text-xs mt-1" style="color:#f5b7b1;">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-xs text-white mb-1 block">Keterangan (opsional)</label>
                        <textarea name="purpose" maxlength="255" class="w-full rounded-lg p-2.5 text-sm"
                            style="background:rgba(255,255,255,0.9);">{{ old('purpose') }}</textarea>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs text-white mb-1 block">Tanggal & Jam Mulai</label>
                            <input type="datetime-local" name="start_datetime" value="{{ old('start_datetime') }}" required
                                class="w-full rounded-lg p-2.5 text-sm" style="background:rgba(255,255,255,0.9);">
                            @error('start_datetime')
                                <p class="text-xs mt-1" style="color:#f5b7b1;">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-xs text-white mb-1 block">Tanggal & Jam Selesai</label>
                            <input type="datetime-local" name="end_datetime" value="{{ old('end_datetime') }}" required
                                class="w-full rounded-lg p-2.5 text-sm" style="background:rgba(255,255,255,0.9);">
                            @error('end_datetime')
                                <p class="text-xs mt-1" style="color:#f5b7b1;">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <hr style="border-color:rgba(255,255,255,0.1);">

                    <div>
                        <label class="text-xs text-white mb-1 block">Nama Pemohon</label>
                        <input type="text" name="guest_name" value="{{ old('guest_name') }}" required minlength="3"
                            maxlength="150" class="w-full rounded-lg p-2.5 text-sm"
                            style="background:rgba(255,255,255,0.9);">
                        @error('guest_name')
                            <p class="text-xs mt-1" style="color:#f5b7b1;">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs text-white mb-1 block">Nomor WhatsApp</label>
                            <input type="tel" name="guest_contact" value="{{ old('guest_contact') }}" required
                                inputmode="numeric" pattern="[0-9]*" minlength="9" maxlength="15"
                                placeholder="08xxxxxxxxxx" class="w-full rounded-lg p-2.5 text-sm"
                                style="background:rgba(255,255,255,0.9);"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            @error('guest_contact')
                                <p class="text-xs mt-1" style="color:#f5b7b1;">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-xs text-white mb-1 block">Instansi/Asal (opsional)</label>
                            <input type="text" name="guest_instansi" value="{{ old('guest_instansi') }}"
                                maxlength="150" class="w-full rounded-lg p-2.5 text-sm"
                                style="background:rgba(255,255,255,0.9);" placeholder="Kosongkan jika masyarakat umum">
                        </div>
                    </div>

                    <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"></div>
                    @error('cf-turnstile-response')
                        <p class="text-xs" style="color:#f5b7b1;">{{ $message }}</p>
                    @enderror

                    <button type="button" id="btn-buka-konfirmasi"
                        class="px-6 py-3 rounded-lg font-bold text-sm self-start"
                        style="background:var(--gold);color:var(--navy);">
                        Ajukan Reservasi
                    </button>
                </form>
            </div>
        </div>
    </section>

    @include('partials.room-reservation-modal')
    <div id="confirm-reservation-modal" onclick="if(event.target===this) tutupModalKonfirmasi()">
        <div class="modal-enter w-full max-w-md rounded-2xl overflow-hidden shadow-2xl" style="background:white;"
            onclick="event.stopPropagation()">

            <div class="p-6" style="background:var(--navy);">
                <h3 class="text-white text-xl leading-snug" style="font-family:'Playfair Display',serif;">
                    Konfirmasi Pengajuan
                </h3>
            </div>

            <div class="p-6 flex flex-col gap-3">
                <p class="text-sm" style="color:var(--navy);">Mohon periksa kembali detail reservasi Anda:</p>

                <div class="flex flex-col gap-2 text-sm p-4 rounded-xl"
                    style="background:var(--cream);color:var(--navy);">
                    <div><strong>Ruangan:</strong> <span id="confirm-ruangan"></span></div>
                    <div><strong>Judul:</strong> <span id="confirm-judul"></span></div>
                    <div><strong>Waktu:</strong> <span id="confirm-waktu"></span></div>
                    <div><strong>Nama:</strong> <span id="confirm-nama"></span></div>
                </div>
            </div>

            <div class="px-6 py-4 flex justify-end gap-3 border-t"
                style="background:var(--cream);border-color:var(--cream-dark);">
                <button type="button" onclick="tutupModalKonfirmasi()"
                    class="px-5 py-2 rounded-lg text-sm font-semibold"
                    style="background:transparent;color:var(--navy);border:1px solid var(--cream-dark);">
                    Batal
                </button>
                <button type="button" id="btn-konfirmasi-submit" class="px-5 py-2 rounded-lg text-sm font-semibold"
                    style="background-color:var(--navy);color:#ffffff;">
                    Ya, Ajukan
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const calEl = document.getElementById('room-availability-calendar');
                if (!calEl || typeof FullCalendar === 'undefined') return;

                const calendar = new FullCalendar.Calendar(calEl, {
                    initialView: 'dayGridMonth',
                    locale: 'id',
                    firstDay: 1,
                    headerToolbar: {
                        left: 'prev,next',
                        center: 'title',
                        right: 'dayGridMonth,listWeek'
                    },
                    buttonText: {
                        today: 'today',
                        month: 'month',
                        listWeek: 'list',
                    },
                    dayHeaderFormat: {
                        weekday: 'short'
                    },
                    moreLinkClick: 'popover',
                    fixedWeekCount: false,
                    showNonCurrentDates: true,
                    displayEventTime: false,
                    eventDisplay: 'block',
                    eventTextColor: '#ffffff',
                    height: 'auto',
                    events: {
                        url: '{{ route('api.room-reservation.kalender') }}',
                        method: 'GET',
                        failure: (err) => console.error('[Reservasi Ruangan] Gagal load kalender:', err),
                    },
                    eventClick(info) {
                        info.jsEvent.preventDefault();
                        info.jsEvent.stopPropagation();

                        const p = info.event.extendedProps;

                        let waktu = p.start_format ?? '';
                        if (p.waktu_mulai) waktu += ` — ${p.waktu_mulai}`;
                        if (p.waktu_selesai) waktu += ` s/d ${p.waktu_selesai}`;

                        window.bukaModalRuangan({
                            judul: info.event.title,
                            ruangan: p.room ?? '—',
                            waktu: waktu,
                            pemohon: p.requestedBy ?? 'Tidak diketahui',
                            status: p.status ?? '',
                            keterangan: p.purpose ?? '',
                        });
                    },
                });
                calendar.render();
            });
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const btnBuka = document.getElementById('btn-buka-konfirmasi');
                const form = document.getElementById('form-reservasi-ruangan');
                const modal = document.getElementById('confirm-reservation-modal');

                if (!btnBuka || !form || !modal) return;

                btnBuka.addEventListener('click', function() {
                    if (!form.reportValidity()) return; // validasi HTML5 native tetap jalan dulu

                    const roomSelect = form.querySelector('[name="room_id"]');
                    const roomLabel = roomSelect.options[roomSelect.selectedIndex]?.text ?? '-';

                    document.getElementById('confirm-ruangan').textContent = roomLabel;
                    document.getElementById('confirm-judul').textContent = form.querySelector('[name="title"]')
                        .value;
                    document.getElementById('confirm-nama').textContent = form.querySelector(
                        '[name="guest_name"]').value;

                    const start = form.querySelector('[name="start_datetime"]').value;
                    const end = form.querySelector('[name="end_datetime"]').value;
                    document.getElementById('confirm-waktu').textContent = `${start} s/d ${end}`;

                    modal.classList.add('modal-open');
                    document.body.style.overflow = 'hidden';
                });

                document.getElementById('btn-konfirmasi-submit').addEventListener('click', function() {
                    form.submit();
                });

                window.tutupModalKonfirmasi = function() {
                    modal.classList.remove('modal-open');
                    document.body.style.overflow = '';
                };
            });
        </script>
    @endpush
@endsection
