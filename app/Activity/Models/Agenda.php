<?php

declare(strict_types=1);

namespace App\Activity\Models;

use App\Models\Bidang;
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

    protected static function newFactory()
    {
        return \Database\Factories\AgendaFactory::new();
    }

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
    }

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

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function bidang(): BelongsToMany
    {
        return $this->belongsToMany(
            Bidang::class,
            'agenda_bidang',
            'agenda_id',
            'bidang_id'
        );
    }

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

    /**
     * Helper ekspresif untuk cek visibilitas publik. is_published SENGAJA
     * tetap boolean di database (Activity tidak butuh alur approval
     * berjenjang seperti Facility/Mobility) — method ini hanya membungkus
     * akses langsung supaya pemanggil tidak perlu tahu representasi
     * internalnya. Tidak ada migrasi kolom, tidak ada perubahan data.
     */
    public function isVisibleToPublic(): bool
    {
        return (bool) $this->is_published;
    }
}
