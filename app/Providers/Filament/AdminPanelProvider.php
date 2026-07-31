<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\CustomLogin;
use App\Classes\CustomPackage\CustomPlugin\CustomFilamentOtpLoginPlugin;
use App\Classes\CustomPackage\CustomPlugin\CustomTranslationManagerPlugin;
use App\Models\Media\Media;
use Awcodes\Curator\Resources\MediaResource;
use CactusGalaxy\FilamentAstrotomic\FilamentAstrotomicTranslatablePlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Config;

use Illuminate\Support\Facades\Lang;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Outerweb\FilamentSettings\Filament\Plugins\FilamentSettingsPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $lng = Lang::locale();
        $brandName =  setting("{$lng}.general.title", "AG ENERGIES CMS") ?? "AG ENERGIES CMS";

        $logo =  Media::find(setting("{$lng}.site.logo"));
        $favicon =  Media::find(setting("{$lng}.site.icon"));


        $filamentPanelID =  setting("general.filament_id", "admin") ?? "admin";
        $filamentPanelPath =  setting("general.filament_path", "admin") ?? "admin";

        $activateOTP =  setting("general.activate_otp",false);

        Config::set('translation-manager.dont_register_navigation_on_panel_ids', [$filamentPanelID]);

        $panel = $panel
                ->default()
                ->id( $filamentPanelID )
                ->path( $filamentPanelPath )
                ->viteTheme('resources/css/filament/admin/theme.css')
                ->profile(isSimple: false)
                ->colors([
                    'primary' => setting("site.admin_panel_color")?? '#C9AC80',
                    'raqmix' => '#98CF8C',
                ])
                ->databaseNotifications()
                ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
                ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
                ->pages([
                    Pages\Dashboard::class,
                ])
                ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
                ->widgets([
                    Widgets\AccountWidget::class,
                    //Widgets\FilamentInfoWidget::class,
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
                ->authMiddleware([
                    Authenticate::class,
                ])
                ->brandName( $brandName )
                ->brandLogo( $logo?->largeUrl )
                ->brandLogoHeight('3.5rem')
                ->favicon( $favicon?->largeUrl )
                ->sidebarCollapsibleOnDesktop()
                ->plugins(array_filter([

                    FilamentAstrotomicTranslatablePlugin::make(),
                    CustomTranslationManagerPlugin::make(),
                    \Awcodes\Curator\CuratorPlugin::make()
                            ->label('Media')
                            ->pluralLabel('Media')
                            ->navigationIcon('heroicon-o-photo')
                            ->navigationGroup('Setting')
                            ->navigationSort(3)
                            ->navigationCountBadge()
                            ->registerNavigation(true)
                            ->defaultListView('grid' || 'list')
                            ->resource(MediaResource::class),

                    FilamentSettingsPlugin::make()
                        ->pages([
                            \App\Filament\Pages\Settings\AllSetting::class,
                        ]),

                    \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),

                    $activateOTP ? CustomFilamentOtpLoginPlugin::make() : null,
                ]))
                ->topbar(true)
                ->navigationGroups([
                    'Setting',
                    'Filament Shield',
                    'Default Enginees',
                    'Galleries',
                    'Engines',
                    'Locations',
                ])
                ->renderHook(
                    PanelsRenderHook::TOPBAR_AFTER,
                    fn (): View => view('vendor.custom.raqmixlogo-middle'),
                )
                ->renderHook(
                    PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
                    fn (): View => view('vendor.custom.raqmixlogo-login'),
                );
            if (!$activateOTP)
            {
                $panel->login(CustomLogin::class);
            }
        return $panel;
    }
}
