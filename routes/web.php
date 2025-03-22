<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScGooMakerController;
use App\Http\Controllers\ScGooModelController;
use App\Http\Controllers\ScGooGradeController;
use App\Http\Controllers\MpmMakerModelController;
use App\Http\Controllers\ScGooMileageController;
use App\Http\Controllers\ScGooYearController;

// sitemap用
use Illuminate\Http\Response;
use App\Models\ScGooMaker;
use App\Models\ScGooModel;
use App\Models\ScGooGrade;
use App\Models\MarketPriceMaster;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Webサイトのルート定義
|
*/

// ホーム
Route::get('/', [MpmMakerModelController::class, 'index'])->name('maker.index');

// モデル一覧
Route::get('/model', [ScGooModelController::class, 'index'])->name('model.index');

// モデル詳細
Route::get('/model/{id}', [ScGooModelController::class, 'show'])->name('model.detail');

// グレード詳細
Route::get('/model/{model_id}/grade/{grade_id}', [ScGooGradeController::class, 'show'])->name('grade.detail');

// 距離別
Route::get('/model/{model_id}/grade/{grade_id}/mileage-{mileage_category}', [ScGooMileageController::class, 'show'])->name('mileage.detail');

// 年式別
Route::get('/model/{model_id}/grade/{grade_id}/year-{year}', [ScGooYearController::class, 'show'])->name('year.detail');

// sitemap.xmlの動的生成
Route::get('/sitemap.xml', function () {
    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    $xml .= '<sitemap><loc>' . url('/sitemap-model.xml') . '</loc></sitemap>';
    $xml .= '<sitemap><loc>' . url('/sitemap-grade.xml') . '</loc></sitemap>';
    $xml .= '<sitemap><loc>' . url('/sitemap-mileage.xml') . '</loc></sitemap>';
    $xml .= '<sitemap><loc>' . url('/sitemap-year.xml') . '</loc></sitemap>';

    $xml .= '</sitemapindex>';

    return response($xml, 200)->header('Content-Type', 'application/xml');
});

// モデル専用サイトマップ
Route::get('/sitemap-model.xml', function () {
    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    $models = ScGooModel::whereIn('id', function ($query) {
        $query->select('model_name_id')->from('market_price_master')->distinct();
    })->latest()->limit(10000)->get();

    foreach ($models as $model) {
        $xml .= '<url>';
        $xml .= '<loc>' . url(route('model.detail', ['id' => $model->id])) . '</loc>';
        $xml .= '<lastmod>' . $model->updated_at->toW3cString() . '</lastmod>';
        $xml .= '<changefreq>weekly</changefreq>';
        $xml .= '<priority>0.7</priority>';
        $xml .= '</url>';
    }

    $xml .= '</urlset>';

    return response($xml, 200)->header('Content-Type', 'application/xml');
});


// グレード専用サイトマップ
Route::get('/sitemap-grade.xml', function () {
    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    $grades = ScGooGrade::whereIn('id', function ($query) {
        $query->select('grade_name_id')->from('market_price_master')->whereNotNull('grade_name_id')->distinct();
    })->latest()->limit(50000)->get();

    foreach ($grades as $grade) {
        $xml .= '<url>';
        $xml .= '<loc>' . url(route('grade.detail', ['model_id' => $grade->model_name_id, 'grade_id' => $grade->id])) . '</loc>';
        $xml .= '<lastmod>' . $grade->updated_at->toW3cString() . '</lastmod>';
        $xml .= '<changefreq>monthly</changefreq>';
        $xml .= '<priority>0.6</priority>';
        $xml .= '</url>';
    }

    $xml .= '</urlset>';

    return response($xml, 200)->header('Content-Type', 'application/xml');
});

// 走行距離専用サイトマップ
Route::get('/sitemap-mileage.xml', function () {
    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    $grades = ScGooGrade::whereIn('id', function ($query) {
        $query->select('grade_name_id')->from('market_price_master')->whereNotNull('mileage')->distinct();
    })->latest()->limit(50000)->get();

    foreach ($grades as $grade) {
        $mileageCategories = MarketPriceMaster::where('model_name_id', $grade->model_name_id)
            ->where('grade_name_id', $grade->id)
            ->pluck('mileage')
            ->map(function ($mileage) {
                return floor($mileage); // 万km単位
            })
            ->unique()
            ->sort();

        foreach ($mileageCategories as $category) {
            $url = route('mileage.detail', [
                'model_id' => $grade->model_name_id,
                'grade_id' => $grade->id,
                'mileage_category' => $category
            ]);
            $xml .= '<url>';
            $xml .= '<loc>' . url($url) . '</loc>';
            $xml .= '<changefreq>monthly</changefreq>';
            $xml .= '<priority>0.4</priority>';
            $xml .= '</url>';
        }
    }

    $xml .= '</urlset>';

    return response($xml, 200)->header('Content-Type', 'application/xml');
});

// 年式専用サイトマップ
Route::get('/sitemap-year.xml', function () {
    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    $grades = ScGooGrade::whereIn('id', function ($query) {
        $query->select('grade_name_id')->from('market_price_master')->whereNotNull('year')->distinct();
    })->latest()->limit(50000)->get();

    foreach ($grades as $grade) {
        $years = MarketPriceMaster::where('model_name_id', $grade->model_name_id)
            ->where('grade_name_id', $grade->id)
            ->pluck('year')
            ->unique()
            ->sort();

        foreach ($years as $year) {
            $url = route('year.detail', [
                'model_id' => $grade->model_name_id,
                'grade_id' => $grade->id,
                'year' => $year
            ]);
            $xml .= '<url>';
            $xml .= '<loc>' . url($url) . '</loc>';
            $xml .= '<changefreq>monthly</changefreq>';
            $xml .= '<priority>0.4</priority>';
            $xml .= '</url>';
        }
    }

    $xml .= '</urlset>';

    return response($xml, 200)->header('Content-Type', 'application/xml');
});
