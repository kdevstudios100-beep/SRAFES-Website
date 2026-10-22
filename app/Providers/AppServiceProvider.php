<?php

namespace App\Providers;

use App\Enum\Permission;
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
        $this->app->scoped(SiteSettingsComposer::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Every model declares $fillable; in development, fail loudly on attributes that would be silently dropped
        Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());

        View::composer(['components.*', 'about', 'contact', 'welcome'], SiteSettingsComposer::class);

        FilamentColor::register([
            'primary' => '#F8D075',
        ]);

        // Non-nullable $user denies guests
        Gate::define('use-translation-manager', function (User $user) {
            return $user->is_active && $user->checkPermissionTo(Permission::ManageSiteContent->value);
        });
    }
}
