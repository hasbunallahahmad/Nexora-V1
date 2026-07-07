@extends('layouts.app')

@section('title', 'Reservasi Ruangan — ' . e(config('app.name')))

@section('content')
    <section class="py-12 sm:py-16" style="background:var(--navy);min-height:100vh;">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">

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
                <div class="rounded-xl p-4 mb-6"
                    style="background:rgba(39,174,96,0.15);border:1px solid rgba(39,174,96,0.4);color:#a8e6c1;">
                    ✅ {{ session('reservation_success') }}
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

                <form method="POST" action="{{ route('room-reservation.store') }}" class="flex flex-col gap-4">
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
                            <label class="text-xs text-white mb-1 block">Kontak WhatsApp/Email</label>
                            <input type="text" name="guest_contact" value="{{ old('guest_contact') }}" required
                                maxlength="150" class="w-full rounded-lg p-2.5 text-sm"
                                style="background:rgba(255,255,255,0.9);">
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

                    <button type="submit" class="px-6 py-3 rounded-lg font-bold text-sm self-start"
                        style="background:var(--gold);color:var(--navy);">
                        Ajukan Reservasi
                    </button>
                </form>
            </div>
        </div>
    </section>

    @push('scripts')
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const calEl = document.getElementById('room-availability-calendar');
                if (!calEl || typeof FullCalendar === 'undefined') return;

                const calendar = new FullCalendar.Calendar(calEl, {
                    initialView: 'dayGridMonth',
                    locale: 'id',
                    height: 'auto',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,listWeek'
                    },
                    events: {
                        url: '{{ route('api.room-reservation.kalender') }}',
                        failure: (err) => console.error('[Reservasi Ruangan] Gagal load kalender:', err),
                    },
                    eventDidMount(info) {
                        if (info.event.extendedProps.room) {
                            info.el.setAttribute('title', info.event.extendedProps.room);
                        }
                    },
                });
                calendar.render();
            });
        </script>
    @endpush
@endsection
