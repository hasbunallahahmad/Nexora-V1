<?php

namespace App\Providers;

// use App\Models\Agenda;
use App\Activity\Models\Agenda as ActivityAgenda;
use App\Observers\AgendaObserver;
use App\Policies\ActivityPolicy;
use App\Policies\AuthenticationLogPolicy;
use Carbon\Carbon;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Rappasoft\LaravelAuthenticationLog\Models\AuthenticationLog;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api-publik', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        RateLimiter::for('reservasi-publik', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        Carbon::setLocale('id');
        // Agenda::observe(AgendaObserver::class);
        ActivityAgenda::observe(AgendaObserver::class);
        Gate::policy(Activity::class, ActivityPolicy::class);
        Gate::policy(AuthenticationLog::class, AuthenticationLogPolicy::class);

        Gate::before(function ($user, $ability) {
            if ($ability === 'delete') {
                return null;
            }

            if ($user->hasRole('super_admin')) {
                return true;
            }
        });

        Event::listen(Login::class, function ($event) {
            Log::info('🔥 Login Event Detected', [
                'user' => $event->user->email,
            ]);

            try {
                DB::table('authentication_log')->insert([
                    'authenticatable_type' => get_class($event->user),
                    'authenticatable_id' => $event->user->id,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'login_at' => now(),
                    'login_successful' => true,
                    'logout_at' => null,
                    'cleared_by_user' => false,
                    'location' => null,
                ]);

                Log::info('✅ Login logged successfully');
            } catch (\Exception $e) {
                Log::error('❌ Failed to log login: ' . $e->getMessage());
            }
        });

        Event::listen(Logout::class, function ($event) {
            Log::info('🚪 Logout Event Detected', [
                'user' => $event->user->email,
            ]);

            try {
                DB::table('authentication_log')
                    ->where('authenticatable_type', get_class($event->user))
                    ->where('authenticatable_id', $event->user->id)
                    ->whereNull('logout_at')
                    ->orderBy('login_at', 'desc')
                    ->limit(1)
                    ->update([
                        'logout_at' => now(),
                    ]);

                Log::info('✅ Logout logged successfully');
            } catch (\Exception $e) {
                Log::error('❌ Failed to log logout: ' . $e->getMessage());
            }
        });
    }
}
