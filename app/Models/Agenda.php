<?php

namespace App\Models;

use App\Models\Bidang;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Agenda extends Model
{
    use HasFactory, LogsActivity;
    protected $table = 'agenda';

    protected $fillable = [
        'judul_agenda',
        'slug',
        'deskripsi',
        'start_date',
        'end_date',
        'location',
        'is_published',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_published' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $agenda): void {
            $agenda->slug = Str::slug($agenda->judul_agenda);
        });

        static::updating(function (self $agenda): void {
            if ($agenda->isDirty('judul_agenda')) {
                $agenda->slug = Str::slug($agenda->judul_agenda);
            }
        });

        // Invalidate caches saat create, update, atau delete
        static::created(function () {
            self::invalidateCaches();
        });

        static::updated(function () {
            self::invalidateCaches();
        });

        static::deleted(function () {
            self::invalidateCaches();
        });
    }

    /**
     * Invalidate agenda-related caches to ensure fresh data
     */
    private static function invalidateCaches(): void
    {
        \Illuminate\Support\Facades\Cache::forget('agenda_stats');

        // Increment kalender cache version untuk force refetch
        $currentVersion = \Illuminate\Support\Facades\Cache::get('kalender_cache_version', 0);
        \Illuminate\Support\Facades\Cache::put('kalender_cache_version', $currentVersion + 1, now()->addHours(24));
    }
    // ══════════════════════════════════════════════════════════════════
    // log aktivitas user
    // supaya audit trail lebih lengkap (misal: siapa yang buat/edit agenda)
    // ══════════════════════════════════════════════════════════════════
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['judul_agenda', 'deskripsi', 'start_date', 'end_date', 'location', 'bidang'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $props = $activity->properties->toArray();

        foreach (['old', 'attributes'] as $key) {
            if (isset($props[$key]['bidang']) && is_array($props[$key]['bidang'])) {
                $props[$key]['bidang'] = collect($props[$key]['bidang'])
                    ->pluck('nama_bidang')
                    ->implode(', ');
            }
        }

        $activity->properties = collect($props);
    }
    // ══════════════════════════════════════════════════════════════════
    // slug untuk URL (misal: /agenda/{slug})
    // supaya URL lebih SEO-friendly dan mudah diingat
    // ══════════════════════════════════════════════════════════════════
    public function getRouteKeyName()
    {
        return 'slug';
    }

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($agenda) {
    //         $agenda->slug = Str::slug($agenda->judul_agenda);
    //     });

    //     static::updating(function ($agenda) {
    //         $agenda->slug = Str::slug($agenda->judul_agenda);
    //     });
    // }

    // ══════════════════════════════════════════════════════════════════
    // relasi many-to-many dengan Bidang (agenda bisa terkait dengan banyak bidang)
    // ══════════════════════════════════════════════════════════════════

    public function bidang(): BelongsToMany
    {
        return $this->belongsToMany(
            Bidang::class,
            'agenda_bidang',
            'agenda_id',
            'bidang_id'
        );
    }

    // ══════════════════════════════════════════════════════════════════
    // scope untuk kueri umum untuk landing page dan kalender
    // supaya kode di controller lebih bersih dan reusable
    // ══════════════════════════════════════════════════════════════════

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('is_published', true);
    }

    public function scopeHariIni(Builder $q): Builder
    {
        return $q->whereDate('start_date', today());
    }

    public function scopeMendatang(Builder $q): Builder
    {
        return $q->where(function (Builder $query): void {
            $query->whereNull('end_date')
                ->orWhere('end_date', '>=', now());
        })
            ->where('start_date', '>', today())
            ->orderBy('start_date')
            ->orderBy('end_date');
    }

    public function scopeMingguIni(Builder $q): Builder
    {
        return $q->whereBetween('start_date', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    public function scopeBetweenDates(Builder $q, string $start, string $end): Builder
    {
        return $q->where('start_date', '<=', $end)
            ->where(function (Builder $query) use ($start): void {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $start);
            });
    }

    // ══════════════════════════════════════════════════════════════════
    // aksesor formating di frontend (misal: format tanggal, nama bidang)
    // ══════════════════════════════════════════════════════════════════

    public function getStartFormatAttribute(): string
    {
        return $this->start_date->translatedFormat('l, d F Y');
    }

    public function getEndFormatAttribute(): ?string
    {
        if (! $this->end_date) return null;
        if ($this->end_date->isSameDay($this->start_date)) return null;
        return $this->end_date->translatedFormat('l, d F Y');
    }

    public function getWaktuMulaiAttribute(): string
    {
        return $this->start_date->format('H:i') . ' WIB';
    }

    public function getWaktuSelesaiAttribute(): ?string
    {
        if (! $this->end_date) return null;
        return $this->end_date->format('H:i') . ' WIB';
    }

    public function getBidangNamesAttribute(): string
    {
        return $this->bidang->pluck('nama_bidang')->join(', ');
    }
}
