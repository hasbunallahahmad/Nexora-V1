<div id="agenda-modal" onclick="if(event.target===this) tutupModal()">
    <div id="agenda-modal-box" class="modal-enter w-full max-w-lg rounded-2xl overflow-hidden shadow-2xl"
        style="background:white;" onclick="event.stopPropagation()">

        {{-- Header --}}
        <div id="modal-header" class="p-6" style="background:var(--navy);">
            <div class="flex justify-between items-start mb-3">
                <div class="flex gap-2 flex-wrap">
                    <span id="modal-tanggal" class="text-xs font-semibold px-3 py-1 rounded-full border"
                        style="background:rgba(201,168,76,0.2);border-color:rgba(201,168,76,0.4);color:var(--gold-light);"></span>
                    <span id="modal-tanggal-selesai" class="text-xs font-semibold px-3 py-1 rounded-full border"
                        style="background:rgba(201,168,76,0.1);border-color:rgba(201,168,76,0.3);color:var(--gold-light);display:none;">
                    </span>
                    <span id="modal-waktu-mulai" class="text-xs font-semibold px-3 py-1 rounded-full"
                        style="background:rgba(255,255,255,0.1);color:rgba(255,255,255,0.8);"></span>
                    <span id="modal-waktu-selesai" class="text-xs font-semibold px-3 py-1 rounded-full"
                        style="background:rgba(255,255,255,0.08);color:rgba(255,255,255,0.6);display:none;"></span>
                </div>
                <button onclick="tutupModal()"
                    class="w-8 h-8 rounded-full border flex items-center justify-center text-white transition-all hover:bg-white/20 shrink-0 ml-2"
                    style="border-color:rgba(255,255,255,0.2);">✕</button>
            </div>
            <h3 id="modal-judul" class="text-white text-xl leading-snug" style="font-family:'Playfair Display',serif;">
            </h3>
        </div>

        {{-- Body --}}
        <div class="p-6 flex flex-col gap-3" style="max-height:60vh;overflow-y:auto;">
            <div class="flex gap-3 items-start p-3 rounded-xl" style="background:var(--cream);">
                <span class="text-xl shrink-0">📍</span>
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider mb-0.5" style="color:#5a6a82;">Lokasi</div>
                    <div id="modal-lokasi" class="text-sm font-medium" style="color:var(--navy);"></div>
                </div>
            </div>

            <div id="modal-bidang-wrap" class="flex gap-3 items-start p-3 rounded-xl"
                style="background:var(--cream);display:none!important;">
                <span class="text-xl shrink-0">🏷️</span>
                <div class="w-full">
                    <div class="text-xs font-bold uppercase tracking-wider mb-1.5" style="color:#5a6a82;">Bidang yang
                        Ditugaskan</div>
                    <div id="modal-bidang" class="flex flex-wrap gap-1.5"></div>
                </div>
            </div>

            <div id="modal-deskripsi-wrap" class="flex gap-3 items-start p-3 rounded-xl"
                style="background:var(--cream);display:none!important;">
                <span class="text-xl shrink-0">📝</span>
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider mb-0.5" style="color:#5a6a82;">Deskripsi
                    </div>
                    <div id="modal-deskripsi" class="text-sm leading-relaxed" style="color:var(--navy);"></div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 flex justify-end border-t"
            style="background:var(--cream);border-color:var(--cream-dark);">
            <button onclick="tutupModal()"
                class="px-5 py-2 rounded-lg text-sm font-semibold transition-all duration-200 ease-in-out hover:scale-105 hover:opacity-90 active:scale-95"
                style="background-color:var(--navy);color:#ffffff;">Tutup</button>
        </div>
    </div>
</div>
