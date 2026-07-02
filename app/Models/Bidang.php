<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Bidang extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'bidang';

    protected $fillable = [
        'nama_bidang',
        'slug',
    ];
    public function agenda(): BelongsToMany
    {
        return $this->belongsToMany(
            Agenda::class,
            'agenda_bidang',
            'bidang_id',
            'agenda_id'
        );
    }
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($bidang) {
            $bidang->slug = Str::slug($bidang->nama_bidang);
        });

        static::updating(function ($bidang) {
            if ($bidang->isDirty('nama_bidang')) {
                $bidang->slug = Str::slug($bidang->nama_bidang);
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getNamaBidangAttribute($value)
    {
        return ucwords($value);
    }

    public function setNamaBidangAttribute($value)
    {
        $this->attributes['nama_bidang'] = strtolower($value);
        $this->attributes['slug'] = Str::slug($value);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('nama_bidang', 'like', "%{$search}%")
            ->orWhere('slug', 'like', "%{$search}%");
    }
}
