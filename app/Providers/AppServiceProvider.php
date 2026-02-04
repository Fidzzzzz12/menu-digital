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
        // Share pending count to all views
        view()->composer('*', function ($view) {
            if (auth()->check()) {
                $pendingOrderCount = \App\Models\Pesanan::where('user_id', auth()->id())
                    ->where('status', 'pending')
                    ->count();
                $view->with('pendingOrderCount', $pendingOrderCount);
            }
        });
    }
}
