<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
// use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\LaravelPasskeys\Models\Concerns\InteractsWithPasskeys;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, LogsActivity;

    public function canAccessPanel(Panel $panel): bool
    {
        if (!$this->hasVerifiedEmail()) {
            return false;
        }

        if (!str_ends_with($this->email, '@dev.dev')) {
            return false;
        }

        if (!$this->is_active) {
            return false;
        }

        // return match ($panel->getId()) {
        //     'admin' => $this->hasAnyRole(['super_admin', 'admin', 'Filament User']),
        //     default => false,
        return $this->roles()->exists();
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getNameAttribute(): string
    {
        return trim($this->attributes['name']);
    }

    public function getActivitylogOptions(): LogOptions
    {
        Log::info('Activity log options dipanggil untuk ' . static::class);

        return LogOptions::defaults()
            ->logAll() // Untuk testing, log semua field dulu
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
