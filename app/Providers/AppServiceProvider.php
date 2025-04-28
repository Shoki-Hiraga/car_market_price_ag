<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use App\Models\MarketPriceMaster;
use App\Models\ModelContents;
use App\Models\MpmMakerModel;
use App\Models\YearGrade;

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

        View::composer('components.year_rule_maker_list', function ($view) {
            $currentYear = date('Y');
            $targetYears = [$currentYear - 25, $currentYear - 24, $currentYear - 23];
    
            $makers = YearGrade::select('maker_name_id')
                ->whereIn('year', $targetYears)
                ->groupBy('maker_name_id')
                ->pluck('maker_name_id')
                ->toArray();
    
            $makerData = DB::table('sc_goo_maker')
                ->whereIn('id', $makers)
                ->orderBy('maker_name')
                ->get();
    
            $view->with('makerData', $makerData);
        });
    
        // View::composer('components.model_contents', function ($view) {
        //     $modelContent = ModelContents::with(['maker', 'model'])->first(); // 例: 1件目を取得
        //     $view->with('modelContent', $modelContent ?? null);
        // });
        
    }
}
