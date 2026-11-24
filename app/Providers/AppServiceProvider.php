<?php

namespace App\Providers;

use App\Services\SiteSettingService;
use App\Services\WilayahService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Satu objek per request, sehingga site_settings cukup dibaca sekali.
        $this->app->scoped(SiteSettingService::class);
        $this->app->scoped(WilayahService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // URL berbahasa Indonesia untuk route resource: /buat dan /{id}/ubah.
        Route::resourceVerbs([
            'create' => 'buat',
            'edit' => 'ubah',
        ]);

        // Variabel $situs tersedia di layout, semua halaman publik, dan halaman masuk pelanggan.
        View::composer(['components.layouts.publik', 'components.kartu-paket', 'publik.*', 'auth.masuk'], function ($view) {
            $view->with('situs', app(SiteSettingService::class));
        });
    }
}
