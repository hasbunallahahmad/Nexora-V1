// Modal Agenda
window.bukaModal = function (data) {
    document.getElementById("modal-judul").textContent = data.judul || "";
    document.getElementById("modal-tanggal").textContent =
        data.start_date || "";
    const elTanggalSelesai = document.getElementById("modal-tanggal-selesai");
    if (data.end_date) {
        elTanggalSelesai.textContent = "s/d " + data.end_date;
        elTanggalSelesai.style.display = "";
    } else {
        elTanggalSelesai.style.display = "none";
    }
    document.getElementById("modal-waktu-mulai").textContent =
        data.waktu_mulai || "";
    document.getElementById("modal-lokasi").textContent = data.lokasi || "—";

    const elSelesai = document.getElementById("modal-waktu-selesai");
    if (data.waktu_selesai && data.waktu_selesai !== data.waktu_mulai) {
        elSelesai.textContent = "s/d " + data.waktu_selesai;
        elSelesai.style.display = "";
    } else {
        elSelesai.style.display = "none";
    }

    const elBidangWrap = document.getElementById("modal-bidang-wrap");
    const elBidang = document.getElementById("modal-bidang");
    elBidang.innerHTML = "";
    if (data.bidang && data.bidang.length > 0) {
        data.bidang.forEach(function (b) {
            const span = document.createElement("span");
            span.className =
                "inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold text-white";
            span.style.background = "var(--navy)";
            span.textContent = b.nama;
            elBidang.appendChild(span);
        });
        elBidangWrap.style.setProperty("display", "flex", "important");
    } else {
        elBidangWrap.style.setProperty("display", "none", "important");
    }

    const elDeskripsiWrap = document.getElementById("modal-deskripsi-wrap");
    const elDeskripsi = document.getElementById("modal-deskripsi");
    if (data.deskripsi) {
        elDeskripsi.innerHTML = data.deskripsi;
        elDeskripsiWrap.style.setProperty("display", "flex", "important");
    } else {
        elDeskripsiWrap.style.setProperty("display", "none", "important");
    }

    document.getElementById("agenda-modal").classList.add("modal-open");
    document.body.style.overflow = "hidden";
};

window.tutupModal = function () {
    document.getElementById("agenda-modal").classList.remove("modal-open");
    document.body.style.overflow = "";
};

document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") window.tutupModal();
});

// JAM DIGITAL
function updateClock() {
    const now = new Date();
    const el = document.getElementById("liveClock");
    const dl = document.getElementById("liveDate");
    if (el)
        el.textContent = now.toLocaleTimeString("id-ID", {
            hour: "2-digit",
            minute: "2-digit",
            second: "2-digit",
        });
    if (dl)
        dl.textContent = now.toLocaleDateString("id-ID", {
            weekday: "long",
            day: "numeric",
            month: "long",
            year: "numeric",
        });
}
updateClock();
setInterval(updateClock, 1000);

// FULLCALENDAR
window.addEventListener("load", () => {
    const calEl = document.getElementById("calendar");
    if (!calEl || typeof FullCalendar === "undefined") {
        console.error("[Kalender] FullCalendar tidak ditemukan.");
        return;
    }

    const apiUrl = window.AgendaConfig?.kalenderUrl ?? "/api/agenda/kalender";

    const calendar = new FullCalendar.Calendar(calEl, {
        initialView: "dayGridMonth",
        locale: "id",
        headerToolbar: {
            left: "prev,next today",
            center: "title",
            right: "dayGridMonth,listWeek",
        },
        buttonText: {
            today: "today",
            month: "month",
            listWeek: "list",
        },
        events: {
            url: apiUrl,
            method: "GET",
            failure: (err) => console.error("[Kalender] Gagal load data:", err),
        },
        eventClick(info) {
            info.jsEvent.preventDefault();
            info.jsEvent.stopPropagation();
            const p = info.event.extendedProps;
            const bidang = (p.bidang ?? []).map((b) => ({
                nama: b.nama ?? b.nama_bidang ?? "",
            }));

            window.bukaModal({
                judul: info.event.title,
                start_date: p.start_format ?? "",
                end_date: p.end_format ?? null,
                waktu_mulai: p.waktu_mulai ?? "",
                waktu_selesai: p.waktu_selesai ?? "",
                lokasi: p.lokasi ?? "",
                bidang,
                deskripsi: p.deskripsi ?? "",
            });
        },
    });

    calendar.render();

    // Store calendar instance untuk digunakan oleh polling script
    if (window.storeCalendarInstance) {
        window.storeCalendarInstance(calendar);
    }
});

// animasi scroll & active link
document.addEventListener("DOMContentLoaded", () => {
    const links = document.querySelectorAll(".nav-link");
    const sectionIds = ["agenda-hari-ini", "agenda-mendatang", "kalender"];

    const setActive = (href) => {
        links.forEach((l) => l.classList.remove("active"));
        [...links]
            .find((l) => l.getAttribute("href") === href)
            ?.classList.add("active");
    };

    links.forEach((link) => {
        link.addEventListener("click", (e) => {
            const href = link.getAttribute("href");
            setActive(href);
            e.preventDefault();

            if (href === "#") {
                window.scrollTo({ top: 0, behavior: "smooth" });
                return;
            }

            const target = document.querySelector(href);
            if (target) {
                const top =
                    target.getBoundingClientRect().top + window.scrollY - 80;
                window.scrollTo({ top, behavior: "smooth" });
            }
        });
    });

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) setActive(`#${entry.target.id}`);
            });
        },
        { threshold: 0.3, rootMargin: "-80px 0px 0px 0px" },
    );

    sectionIds.forEach((id) => {
        const el = document.getElementById(id);
        if (el) observer.observe(el);
    });

    setActive("#");
    const scrollTopBtn = document.getElementById("scrollTopBtn");
    const scrollRingEl = document.getElementById("scrollRingProgress");

    if (scrollTopBtn) {
        const CIRCUMFERENCE = 2 * Math.PI * 22;

        function updateScrollProgress() {
            const scrollTop = window.scrollY;
            const docHeight =
                document.documentElement.scrollHeight - window.innerHeight;
            const progress = docHeight > 0 ? scrollTop / docHeight : 0;
            scrollTopBtn.classList.toggle("show", scrollTop > 150);
            if (scrollRingEl) {
                const offset = CIRCUMFERENCE * (1 - progress);
                scrollRingEl.style.strokeDashoffset = offset.toFixed(2);
            }
        }

        updateScrollProgress();

        window.addEventListener("scroll", updateScrollProgress, {
            passive: true,
        });

        scrollTopBtn.addEventListener("click", () => {
            window.scrollTo({ top: 0, behavior: "smooth" });
        });
    }
});
