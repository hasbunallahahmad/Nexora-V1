<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{--
        Security headers via <meta>.
        CATATAN: Untuk produksi wajib set juga via HTTP header di Laravel middleware
        karena meta tag lebih lemah dari HTTP header.
        CSP TIDAK diset di sini — CSP via meta tag memblokir CDN (penyebab CORB error).
    --}}
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="SAMEORIGIN">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <title>@yield('title', e(config('app.name')))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/css/agenda.css', 'resources/js/app.js', 'resources/js/agenda.js'])
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js" defer></script>

    @yield('config')
    @stack('styles')
</head>

<body class="app-body">
    <header class="app-header sticky top-0 z-50" role="banner">
        <div class="app-header__inner max-w-6xl mx-auto px-4 sm:px-6">

            <a href="{{ url('/') }}" class="app-header__brand"
                aria-label="{{ e(config('app.name')) }} — Kembali ke Beranda">
                <img src="{{ asset('images/logo.png') }}" alt="Logo Arpusda Kota Semarang"
                    class="app-header__logo w-9 h-9 sm:w-11 sm:h-11 object-contain" width="44" height="44">
                <div>
                    <p class="app-header__title text-base sm:text-lg">
                        {{ e(config('app.name', 'Agenda Pemerintahan')) }}
                    </p>
                    <p class="app-header__subtitle text-xs">
                        Informasi Kegiatan Arpusda Kota Semarang
                    </p>
                </div>
            </a>

            <nav class="app-header__nav" aria-label="Navigasi utama" style="display:flex;align-items:center;gap:1rem;">
                {{-- @foreach ([['#', 'Beranda'], ['#agenda-hari-ini', 'Hari Ini'], ['#agenda-mendatang', 'Mendatang'], ['#kalender', 'Kalender'], [route('room-reservation.index'), 'Reservasi Ruangan']] as [$href, $label])
                 --}}
                @foreach ([[url('/'), 'Beranda'], [url('/') . '#agenda-hari-ini', 'Hari Ini'], [url('/') . '#agenda-mendatang', 'Mendatang'], [url('/') . '#kalender', 'Kalender'], [route('room-reservation.index'), 'Reservasi Ruangan']] as [$href, $label])
                    <a href="{{ $href }}"
                        class="nav-link text-sm font-medium focus-visible:ring-2 focus-visible:ring-yellow-400 focus-visible:ring-offset-1">
                        {{ $label }}
                    </a>
                @endforeach

                <a href="{{ url('/pengelola-kegiatan/login') }}" aria-label="" title="Login Pengelola" target="_blank"
                    style="display:flex;align-items:center;opacity:0.5;transition:opacity 0.2s;"
                    onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.5'"
                    class="focus-visible:ring-2 focus-visible:ring-yellow-400 focus-visible:ring-offset-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24"
                        fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                </a>
            </nav>
        </div>
    </header>

    <main id="main-content" role="main">
        @yield('content')
    </main>

    <footer class="app-footer" role="contentinfo">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="footer-grid mb-8 sm:mb-10">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo Arpusda Kota Semarang"
                            class="w-10 h-10 object-contain" width="40" height="40">
                        <div>
                            <p class="footer-brand-name">{{ e(config('app.name')) }}</p>
                            <p class="footer-brand-sub text-xs">Informasi Kegiatan Arpusda Kota Semarang</p>
                        </div>
                    </div>
                    <p class="footer-desc text-sm leading-relaxed">
                        Portal resmi informasi agenda dan jadwal kegiatan dinas pemerintahan.
                    </p>
                </div>

                <div>
                    <p class="footer-heading text-xs font-bold tracking-widest uppercase mb-4">Tautan Cepat</p>
                    <ul class="flex flex-col gap-2" role="list">
                        <li><a href="{{ url('/') }}" class="footer-link text-sm">Beranda</a></li>
                        <li><a href="https://arpusda.semarangkota.go.id/" target="_blank" rel="noopener noreferrer"
                                class="footer-link text-sm">Website Dinas</a></li>
                        <li><a href="https://ppid.arpusda.semarangkota.go.id/" target="_blank" rel="noopener noreferrer"
                                class="footer-link text-sm">PPID</a></li>
                    </ul>
                </div>

                <div>
                    <p class="footer-heading text-xs font-bold tracking-widest uppercase mb-4">Kontak</p>
                    <ul class="flex flex-col gap-2 text-sm footer-contact" role="list">
                        <li>📧 <a
                                href="/cdn-cgi/l/email-protection#304b4b105518535f5e56595718175d51595c1e56425f5d1e51545442554343171c1017595e565f7054595e51431e575f1e5954171919104d4d"
                                class="footer-link break-all">
                                {{ e(config('mail.from.address', '<span class="__cf_email__" data-cfemail="70191e161f3014191e11035e171f5e1914">[email&#160;protected]</span>')) }}
                            </a></li>
                        <li>📞 (024) 7466-215</li>
                        <li class="leading-snug">📍 Jl. Prof. Sudarto No. 116, Sumurboto, Banyumanik, Semarang</li>
                    </ul>
                </div>

                <div>
                    <p class="footer-heading text-xs font-bold tracking-widest uppercase mb-4">Ikuti Kami</p>
                    <div class="footer-sosmed flex gap-2 mb-3 flex-wrap">
                        <a href="https://www.instagram.com/dinasarpus_semarang" target="_blank"
                            rel="noopener noreferrer" aria-label="Instagram kami"
                            class="sosmed-btn sosmed-btn--ig focus-visible:ring-2 focus-visible:ring-yellow-400 focus-visible:ring-offset-1">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                aria-hidden="true">
                                <rect x="2" y="2" width="20" height="20" rx="5" stroke="white"
                                    stroke-width="2" fill="none" />
                                <circle cx="12" cy="12" r="4" stroke="white" stroke-width="2"
                                    fill="none" />
                                <circle cx="17.5" cy="6.5" r="1.2" fill="white" />
                            </svg>
                        </a>
                        <a href="https://www.youtube.com/@dinasarpuskotasemarang2232" target="_blank"
                            rel="noopener noreferrer" aria-label="YouTube kami"
                            class="sosmed-btn sosmed-btn--yt focus-visible:ring-2 focus-visible:ring-yellow-400 focus-visible:ring-offset-1">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="white"
                                aria-hidden="true">
                                <path
                                    d="M23 7s-.3-1.9-1.1-2.7c-1-.9-2.2-1-2.7-1C16.5 3 12 3 12 3s-4.5 0-7.2.3c-.5.1-1.7.1-2.7 1C1.3 5.1 1 7 1 7S.7 9.1.7 11.2v2c0 2 .3 4.1.3 4.1s.3 1.9 1.1 2.7c1 .9 2.4.9 3 1C7.2 21.1 12 21 12 21s4.5 0 7.2-.3c.5-.1 1.7-.1 2.7-1 .8-.8 1.1-2.7 1.1-2.7s.3-2.1.3-4.1v-2C23.3 9.1 23 7 23 7zM9.7 15.5V8.4l7.3 3.6-7.3 3.5z" />
                            </svg>
                        </a>
                        <a href="https://wa.me/6281222233860" target="_blank" rel="noopener noreferrer"
                            aria-label="WhatsApp kami"
                            class="sosmed-btn sosmed-btn--wa focus-visible:ring-2 focus-visible:ring-yellow-400 focus-visible:ring-offset-1">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="white"
                                aria-hidden="true">
                                <path
                                    d="M17.5 14.4c-.3-.1-1.7-.8-2-.9-.3-.1-.5-.1-.7.1-.2.3-.8.9-.9 1.1-.2.2-.3.2-.6.1s-1.2-.4-2.3-1.4c-.9-.8-1.5-1.7-1.6-2-.2-.3 0-.5.1-.6l.4-.5c.1-.2.2-.3.2-.5s-.1-.4-.2-.5C10.6 9 10 7.5 9.8 7c-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3C7.8 7.2 7 8 7 9.7c0 1.7 1.2 3.3 1.4 3.5.1.2 2.5 3.8 6 5.3.8.3 1.5.5 2 .7.8.2 1.6.2 2.2.1.7-.1 2-.8 2.3-1.6.3-.8.3-1.4.2-1.5-.1-.3-.3-.3-.6-.4z" />
                                <path
                                    d="M12 2C6.5 2 2 6.5 2 12c0 1.9.5 3.7 1.4 5.2L2 22l4.9-1.3C8.3 21.5 10.1 22 12 22c5.5 0 10-4.5 10-10S17.5 2 12 2zm0 18.2c-1.7 0-3.4-.5-4.8-1.3l-.3-.2-3 .8.8-2.9-.2-.3C3.7 15.1 3.2 13.6 3.2 12 3.2 7.1 7.1 3.2 12 3.2S20.8 7.1 20.8 12 16.9 20.2 12 20.2z" />
                            </svg>
                        </a>
                        <button type="button" id="btn-open-sosmed" aria-haspopup="dialog"
                            aria-controls="modal-sosmed" aria-expanded="false" aria-label="Lihat semua sosial media"
                            class="sosmed-btn sosmed-btn--more focus-visible:ring-2 focus-visible:ring-yellow-400 focus-visible:ring-offset-1">
                            +3
                        </button>
                    </div>
                    <p class="footer-tagline text-xs leading-relaxed">
                        Ikuti kami untuk informasi &amp; kegiatan terbaru.
                    </p>
                </div>

            </div>

            <div class="footer-bottom border-t pt-5">
                <p class="text-xs footer-copy">
                    &copy; {{ date('Y') }} {{ e(config('app.name')) }} &mdash; Ver 2.0
                </p>
                <p class="text-xs footer-copy">IT Arpusda Kota Semarang Made with ❤️</p>
            </div>
        </div>
    </footer>

    <button id="scrollTopBtn" type="button" aria-label="Kembali ke atas halaman"
        class="scroll-top-btn fixed bottom-6 right-6 z-50 focus-visible:ring-2 focus-visible:ring-yellow-400 focus-visible:ring-offset-2">
        <svg id="scrollProgressRing" width="52" height="52" viewBox="0 0 52 52" aria-hidden="true">
            <circle class="scroll-ring__track" cx="26" cy="26" r="22" fill="none"
                stroke-width="4" />
            <circle id="scrollRingProgress" class="scroll-ring__progress" cx="26" cy="26" r="22"
                fill="none" stroke-width="4" stroke-linecap="round" transform="rotate(-90 26 26)" />
            <circle class="scroll-ring__bg" cx="26" cy="26" r="19" />
            <path class="scroll-ring__arrow" d="M26 31 L26 19 M20 25 L26 19 L32 25" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round" fill="none" />
        </svg>
    </button>

    @include('partials.agenda-modal')
    <div id="modal-sosmed" role="dialog" aria-modal="true" aria-labelledby="sosmed-modal-title" aria-hidden="true"
        tabindex="-1">
        <div class="modal-enter modal-box">

            <div class="modal-header">
                <div>
                    <p class="modal-eyebrow">Temukan Kami Di</p>
                    <h3 id="sosmed-modal-title" class="modal-title">Sosial Media</h3>
                </div>
                <button type="button" id="btn-close-sosmed" aria-label="Tutup modal sosial media"
                    class="modal-close-btn focus-visible:ring-2 focus-visible:ring-yellow-400 focus-visible:ring-offset-1">✕</button>
            </div>

            <div class="modal-body">
                @php
                    $sosmedItems = [
                        [
                            'url' => 'https://www.instagram.com/dinasarpus_semarang/',
                            'label' => 'Instagram',
                            'sub' => '@dinasarpus_semarang',
                            'bg' => 'linear-gradient(135deg,#f09433,#dc2743,#bc1888)',
                            'svg' =>
                                '<rect x="2" y="2" width="20" height="20" rx="5" stroke="white" stroke-width="2" fill="none"/><circle cx="12" cy="12" r="4" stroke="white" stroke-width="2" fill="none"/><circle cx="17.5" cy="6.5" r="1.2" fill="white"/>',
                        ],
                        [
                            'url' => 'https://www.youtube.com/@dinasarpuskotasemarang2232',
                            'label' => 'YouTube',
                            'sub' => '@dinasarpuskotasemarang2232',
                            'bg' => '#FF0000',
                            'svg' =>
                                '<path d="M23 7s-.3-1.9-1.1-2.7c-1-.9-2.2-1-2.7-1C16.5 3 12 3 12 3s-4.5 0-7.2.3c-.5.1-1.7.1-2.7 1C1.3 5.1 1 7 1 7S.7 9.1.7 11.2v2c0 2 .3 4.1.3 4.1s.3 1.9 1.1 2.7c1 .9 2.4.9 3 1C7.2 21.1 12 21 12 21s4.5 0 7.2-.3c.5-.1 1.7-.1 2.7-1 .8-.8 1.1-2.7 1.1-2.7s.3-2.1.3-4.1v-2C23.3 9.1 23 7 23 7zM9.7 15.5V8.4l7.3 3.6-7.3 3.5z" fill="white"/>',
                        ],
                        [
                            'url' => 'https://wa.me/6281222233860',
                            'label' => 'WhatsApp',
                            'sub' => '+6281222233860',
                            'bg' => '#25D366',
                            'svg' =>
                                '<path d="M17.5 14.4c-.3-.1-1.7-.8-2-.9-.3-.1-.5-.1-.7.1-.2.3-.8.9-.9 1.1-.2.2-.3.2-.6.1s-1.2-.4-2.3-1.4c-.9-.8-1.5-1.7-1.6-2-.2-.3 0-.5.1-.6l.4-.5c.1-.2.2-.3.2-.5s-.1-.4-.2-.5C10.6 9 10 7.5 9.8 7c-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3C7.8 7.2 7 8 7 9.7c0 1.7 1.2 3.3 1.4 3.5.1.2 2.5 3.8 6 5.3.8.3 1.5.5 2 .7.8.2 1.6.2 2.2.1.7-.1 2-.8 2.3-1.6.3-.8.3-1.4.2-1.5-.1-.3-.3-.3-.6-.4z" fill="white"/><path d="M12 2C6.5 2 2 6.5 2 12c0 1.9.5 3.7 1.4 5.2L2 22l4.9-1.3C8.3 21.5 10.1 22 12 22c5.5 0 10-4.5 10-10S17.5 2 12 2zm0 18.2c-1.7 0-3.4-.5-4.8-1.3l-.3-.2-3 .8.8-2.9-.2-.3C3.7 15.1 3.2 13.6 3.2 12 3.2 7.1 7.1 3.2 12 3.2S20.8 7.1 20.8 12 16.9 20.2 12 20.2z" fill="white"/>',
                        ],
                        [
                            'url' => 'https://www.tiktok.com/@dinasarpus_kotasemarang',
                            'label' => 'TikTok',
                            'sub' => '@dinasarpus_kotasemarang',
                            'bg' => '#010101',
                            'svg' =>
                                '<path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01a6.32 6.32 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.33-6.34V8.69a8.19 8.19 0 0 0 4.79 1.52V6.76a4.85 4.85 0 0 1-1.02-.07z" fill="white"/>',
                        ],
                        [
                            'url' => 'https://www.facebook.com/groups/dinasarpus.semarangkota',
                            'label' => 'Facebook',
                            'sub' => '@dinasarpus.semarangkota',
                            'bg' => '#1877F2',
                            'svg' =>
                                '<path d="M24 12.07C24 5.41 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.04V9.41c0-3.02 1.8-4.7 4.54-4.7 1.31 0 2.68.24 2.68.24v2.97h-1.5c-1.5 0-1.96.93-1.96 1.89v2.26h3.32l-.53 3.5h-2.8V24C19.62 23.1 24 18.1 24 12.07z" fill="white"/>',
                        ],
                        [
                            'url' => 'https://x.com/dinarpus_smg',
                            'label' => 'X (Twitter)',
                            'sub' => '@dinarpus_smg',
                            'bg' => '#000',
                            'svg' =>
                                '<path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.748l7.73-8.835L1.254 2.25H8.08l4.253 5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z" fill="white"/>',
                        ],
                    ];
                @endphp

                @foreach ($sosmedItems as $sm)
                    <a href="{{ $sm['url'] }}" target="_blank" rel="noopener noreferrer"
                        aria-label="Buka {{ $sm['label'] }} di tab baru"
                        class="sosmed-list-item focus-visible:ring-2 focus-visible:ring-yellow-400 focus-visible:ring-offset-1">
                        <div class="sosmed-list-item__icon" style="background:{{ $sm['bg'] }};">
                            <svg width="16" height="16" viewBox="0 0 24 24"
                                aria-hidden="true">{!! $sm['svg'] !!}</svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="sosmed-list-item__name">{{ $sm['label'] }}</p>
                            <p class="sosmed-list-item__sub truncate">{{ $sm['sub'] }}</p>
                        </div>
                        <span class="sosmed-list-item__arrow" aria-hidden="true">↗</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
    <script>
        (function() {
            'use strict';

            function openModal(id, tid) {
                var m = document.getElementById(id);
                var t = tid ? document.getElementById(tid) : null;
                if (!m) return;
                m.classList.add('modal-open');
                m.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
                if (t) t.setAttribute('aria-expanded', 'true');
                setTimeout(function() {
                    m.focus();
                }, 50);
            }

            function closeModal(id, tid) {
                var m = document.getElementById(id);
                var t = tid ? document.getElementById(tid) : null;
                if (!m) return;
                m.classList.remove('modal-open');
                m.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
                if (t) {
                    t.setAttribute('aria-expanded', 'false');
                    t.focus();
                }
            }
            document.addEventListener('DOMContentLoaded', function() {
                var o = document.getElementById('btn-open-sosmed');
                var c = document.getElementById('btn-close-sosmed');
                var m = document.getElementById('modal-sosmed');
                if (o) o.addEventListener('click', function() {
                    openModal('modal-sosmed', 'btn-open-sosmed');
                });
                if (c) c.addEventListener('click', function() {
                    closeModal('modal-sosmed', 'btn-open-sosmed');
                });
                if (m) m.addEventListener('click', function(e) {
                    if (e.target === m) closeModal('modal-sosmed', 'btn-open-sosmed');
                });
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && m && m.classList.contains('modal-open'))
                        closeModal('modal-sosmed', 'btn-open-sosmed');
                });
            });
        })();
    </script>

    @stack('scripts')

</body>

</html>
