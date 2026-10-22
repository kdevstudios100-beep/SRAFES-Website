<?php

namespace App\View\Composers;

use App\Models\SiteSetting;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SiteSettingsComposer
{
    public Collection $siteSettings;

    public function __construct()
    {
        // Views index these keys directly, so default them to null when settings are missing
        $this->siteSettings = collect([
            'name' => null,
            'address' => null,
            'email' => null,
            'phone' => null,
            'whatsapp' => null,
            'instagram_url' => null,
            'facebook_url' => null,
            'x_url' => null,
            'google_maps_embed_url' => null,
            'home_page_video_url' => null,
        ])->merge(SiteSetting::value('settings') ?? []);
    }

    public function compose(View $view)
    {
        $view->with('siteSettings', $this->siteSettings);
    }
}
