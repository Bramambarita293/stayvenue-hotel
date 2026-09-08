<?php

namespace App\Providers;

use App\Models\Hall;
use App\Models\Reservation;
use App\Models\RoomType;
use App\Policies\ReservationPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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

        if (config('app.env') !== 'local' || isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            URL::forceScheme('https');
        }
        Gate::policy(Reservation::class, ReservationPolicy::class);

        View::composer('*', function ($view) {
            $view->with('site', config('site'));
        });

        View::composer(['auth.login'], function ($view) {
            $view->with('authCover', static::coverImage(\App\Models\SiteSetting::get(\App\Models\SiteSetting::AUTH_LOGIN_COVER)));
        });

        View::composer(['auth.register'], function ($view) {
            $view->with('authCover', static::coverImage(\App\Models\SiteSetting::get(\App\Models\SiteSetting::AUTH_REGISTER_COVER)));
        });
    }

    /**
     * Sampul auth berlapis: setting admin -> foto produk -> bawaan.
     */
    protected static function coverImage(?string $configured = null): string
    {
        if ($configured) {
            $url = \App\Support\HotelImage::url($configured);
            if ($url !== '') {
                return $url;
            }
        }

        $images = collect()
            ->merge(Hall::where('is_active', true)->pluck('images')->flatten())
            ->merge(RoomType::pluck('images')->flatten())
            ->filter()
            ->values();

        if ($image = $images->first()) {
            return \App\Support\HotelImage::url($image, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?q=80&w=1170&auto=format&fit=crop');
        }

        return 'https://images.unsplash.com/photo-1566073771259-6a8506099945?q=80&w=1170&auto=format&fit=crop';
    }
}
