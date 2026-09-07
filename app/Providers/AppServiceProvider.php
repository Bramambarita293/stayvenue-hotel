<?php

namespace App\Providers;

use App\Models\Hall;
use App\Models\Reservation;
use App\Models\RoomType;
use App\Policies\ReservationPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
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

        View::composer(['auth.login', 'auth.register'], function ($view) {
            $view->with('authCover', static::coverImage());
        });
    }

    /**
     * Resolve a real cover image from seeded room/hall photos.
     */
    protected static function coverImage(): string
    {
        $images = collect()
            ->merge(Hall::where('is_active', true)->pluck('images')->flatten())
            ->merge(RoomType::pluck('images')->flatten())
            ->filter()
            ->values();

        if ($image = $images->first()) {
            return Storage::url($image);
        }

        return 'https://images.unsplash.com/photo-1566073771259-6a8506099945?q=80&w=1170&auto=format&fit=crop';
    }
}
