<?php

namespace App\Providers\Filament;


use AchyutN\FilamentLogViewer\FilamentLogViewer;
use AlizHarb\ActivityLog\ActivityLogPlugin;
use App\Facility\Filament\Resources\Rooms\RoomResource;
use App\Helpers\PexelsHelper;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Caresome\FilamentAuthDesigner\AuthDesignerPlugin;
use Caresome\FilamentAuthDesigner\Data\AuthPageConfig;
use Caresome\FilamentAuthDesigner\Enums\MediaPosition;
use Devonab\FilamentEasyFooter\EasyFooterPlugin;
use EightCedars\FilamentInactivityGuard\FilamentInactivityGuardPlugin;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;
use Joaopaulolndev\FilamentEditProfile\Pages\EditProfilePage;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;
use Sanzgrapher\DraggableModal\DraggableModalPlugin;
use SpyApp\ThemeAberdeen\ThemeAberdeenPlugin;
use Tapp\FilamentAuthenticationLog\FilamentAuthenticationLogPlugin;
use WatheqAlshowaiter\FilamentStickyTableHeader\StickyTableHeaderPlugin;
use App\Facility\Filament\Resources\RoomReservations\RoomReservationResource;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->favicon(asset('favicon.ico'))
            ->id('admin')
            ->path('pengelola-kegiatan')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->userMenuItems([
                'profile' => Action::make('profile')
                    ->label(fn() => Auth::user()->name)
                    ->url(fn(): string => EditProfilePage::getUrl())
                    ->icon('heroicon-m-user-circle'),
            ])
            ->breadcrumbs(false)
            ->font('Poppins')
            ->brandName(config('app.name'))
            ->brandLogo(asset('images/logo.png'))
            ->topNavigation()
            ->authGuard('web')
            ->sidebarWidth('15rem')
            ->maxContentWidth(Width::Full)
            ->spa()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->resources([
                RoomResource::class,
                RoomReservationResource::class,
            ])
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                //
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                AuthDesignerPlugin::make()
                    ->login(
                        fn(AuthPageConfig $config) => $config
                            // ->media('https://images.unsplash.com/photo-1486325212027-8081e485255e?w=1920&q=80&fit=crop')
                            ->media(PexelsHelper::getDailyImage())
                            ->mediaPosition(MediaPosition::Cover)
                            ->blur('2')
                    )
                    ->themeToggle(),
                ThemeAberdeenPlugin::make(),
                FilamentFullCalendarPlugin::make()
                    ->selectable()
                    ->editable()
                    ->timezone('Asia/Jakarta')
                    ->locale('id'),
                FilamentShieldPlugin::make(),
                FilamentAuthenticationLogPlugin::make(),
                StickyTableHeaderPlugin::make()
                    ->shouldScrollToTopOnPageChanged(enabled: true, behavior: 'smooth'),
                ActivityLogPlugin::make()
                    ->label('Activity Log')
                    ->pluralLabel('Activity Logs')
                    ->navigationGroup('System'),
                FilamentLogViewer::make()
                    ->authorize(fn(): bool =>  Auth::check() &&  Auth::user()->can('View:LogTable')),
                DraggableModalPlugin::make(),
                FilamentInactivityGuardPlugin::make()
                    ->enabled(!app()->isLocal()),
                EasyFooterPlugin::make()
                    ->withLoadTime('This page loaded in')
                    ->withBorder()
                    ->withLogo(asset('images/logo.png'))
                    ->withSentence('Made with ❤️ by : Arpusda | Ver. 2.0'),
                FilamentEditProfilePlugin::make()
                    ->canAccess(fn() => Auth::user()->id === 1)
                    ->shouldRegisterNavigation(false),
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
