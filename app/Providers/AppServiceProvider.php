<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        if (str_starts_with(config('app.url'), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $webSubtitle = \Illuminate\Support\Facades\DB::table('settings')->where('key', 'web_subtitle')->value('value') ?? 'สพป.ชพ.1';
                \Illuminate\Support\Facades\View::share('webSubtitle', $webSubtitle);
            } else {
                \Illuminate\Support\Facades\View::share('webSubtitle', 'สพป.ชพ.1');
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\View::share('webSubtitle', 'สพป.ชพ.1');
        }
    }
}
