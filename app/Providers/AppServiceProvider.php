<?php

namespace App\Providers;

use App\Models\User;
use App\View\Composers\SiteSettingsComposer;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // One instance per request, so site settings are queried once instead of per view
        $this->app->singleton(SiteSettingsComposer::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();

        View::composer(['components.*', 'about', 'contact', 'welcome'], SiteSettingsComposer::class);

        FilamentColor::register([
            'primary' => '#F8D075',
        ]);

        // Non-nullable $user denies guests; only logged-in panel users may manage translations
        Gate::define('use-translation-manager', function (User $user) {
            return true;
        });
    }
}
