<?php

namespace App\Providers\Filament;

use App\Filament\Pages\AttendanceOverview;
use App\Filament\Pages\AttendanceReport;
use App\Filament\Pages\TakeAttendance;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * The teachers' own panel. It holds only the attendance pages, which show a
 * teacher nothing beyond the classes they are class teacher of. Access is
 * checked in User::canAccessPanel().
 */
class TeacherPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('teacher')
            ->path('teacher')
            ->brandName('SRAFES Teachers')
            ->login()
            ->profile()
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->pages([
                AttendanceOverview::class,
                TakeAttendance::class,
                AttendanceReport::class,
            ])
            ->homeUrl(fn () => AttendanceOverview::getUrl(panel: 'teacher'))
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
            ]);
    }
}
