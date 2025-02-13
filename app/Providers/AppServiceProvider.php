<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\MarketPriceMaster;

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
    public function boot()
    {
        // components.footer が呼び出されたときのみデータを取得する
        View::composer('components.footer', function ($view) {
            $sc_goo_maker = MarketPriceMaster::with('maker')
                ->get()
                ->unique('maker_name_id')
                ->pluck('maker');

            $view->with('sc_goo_maker', $sc_goo_maker);
        });
    }
}
