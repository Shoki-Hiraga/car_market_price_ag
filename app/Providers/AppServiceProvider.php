<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\MarketPriceMaster;
use App\Models\ModelContents;
use App\Models\MpmMakerModel;


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
            $sc_goo_maker = MpmMakerModel::orderBy('maker_name_id')->pluck('mpm_maker_name')->unique();

            $view->with('sc_goo_maker', $sc_goo_maker);
        });

        // components.model_contents が呼び出されたときのみデータを取得する
        View::composer('components.model_contents', function ($view) {
            $model_contents = ModelContents::with(['maker', 'model'])->get();

            $view->with('model_contents', $model_contents);
        });

        // View::composer('components.model_contents', function ($view) {
        //     $modelContent = ModelContents::with(['maker', 'model'])->first(); // 例: 1件目を取得
        //     $view->with('modelContent', $modelContent ?? null);
        // });
        
    }
}
