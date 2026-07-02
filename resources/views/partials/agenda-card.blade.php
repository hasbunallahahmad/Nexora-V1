<div class="rounded-2xl overflow-hidden border cursor-pointer transition-all duration-200 hover:-translate-y-1 shadow-sm hover:shadow-xl"
    style="background:white;border-color:var(--cream-dark);"
    onclick="bukaModal({
        judul:         {{ Js::from($agenda->judul_agenda) }},
        start_date:    {{ Js::from($agenda->start_format) }},
        end_date:      {{ Js::from($agenda->end_format) }},
        waktu_mulai:   {{ Js::from($agenda->waktu_mulai) }},
        waktu_selesai: {{ Js::from($agenda->waktu_selesai) }},
        lokasi:        {{ Js::from($agenda->location) }},
        bidang:        {{ Js::from($agenda->bidang->map(fn($b) => ['id' => $b->id, 'nama' => $b->nama_bidang])->values()) }},
        deskripsi:     {{ Js::from($agenda->deskripsi) }},
    })">
    {{-- ── Card Header: tanggal & jam ── --}}
    <div class="px-5 py-3 flex justify-between items-center" style="background:var(--navy);">
        <span class="text-xs font-semibold" style="color:var(--gold-light);">
            📅 {{ $agenda->start_date->translatedFormat('d M Y') }}
        </span>
        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full"
            style="background:rgba(201,168,76,0.15);color:var(--gold-light);">
            ⏰ {{ $agenda->waktu_mulai }}
        </span>
    </div>

    {{-- ── Card Body ── --}}
    <div class="p-5">
        <h3 class="font-semibold mb-3 leading-snug" style="color:var(--navy);font-size:0.92rem;">
            {{ $agenda->judul_agenda }}
        </h3>

        <div class="flex flex-col gap-1.5 mb-3">
            @if ($agenda->location)
                <div class="flex gap-2 items-start text-xs" style="color:#5a6a82;">
                    <span class="mt-0.5 shrink-0">📍</span>
                    <span>{{ $agenda->location }}</span>
                </div>
            @endif

            @if ($agenda->end_date && $agenda->end_date->format('d') !== $agenda->start_date->format('d'))
                <div class="flex gap-2 items-start text-xs" style="color:#5a6a82;">
                    <span class="mt-0.5 shrink-0">🏁</span>
                    <span>Selesai: {{ $agenda->end_date->translatedFormat('d M Y') }} •
                        {{ $agenda->waktu_selesai }}</span>
                </div>
            @elseif ($agenda->end_date && $agenda->end_date != $agenda->start_date)
                <div class="flex gap-2 items-start text-xs" style="color:#5a6a82;">
                    <span class="mt-0.5 shrink-0">⏱️</span>
                    <span>s/d {{ $agenda->waktu_selesai }}</span>
                </div>
            @endif
        </div>

        @if ($agenda->bidang->isNotEmpty())
            <div class="flex flex-wrap gap-1.5 mt-2">
                @foreach ($agenda->bidang as $b)
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold text-white"
                        style="background:var(--navy-mid, #162848);">
                        {{ $b->nama_bidang }}
                    </span>
                @endforeach
            </div>
        @endif
    </div>
</div>
