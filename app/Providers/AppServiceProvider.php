<?php

namespace App\Providers;

use App\Models\Merchant;
use Illuminate\Support\Facades\View;
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
        View::composer('layouts.merchant', function ($view) {
        $merchant = auth()->user()?->merchant;

        $view->with('pendingCount', $merchant
            ? $merchant->orders()->where('status', 'menunggu')->count()
            : 0);
        });

        View::composer(['layouts.customer', 'customer.*'], function ($view) {
        $view->with('storeOpen', Merchant::isStoreOpen());
        });
    }
}
