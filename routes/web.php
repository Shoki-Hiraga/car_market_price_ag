<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScGooMakerController;
use App\Http\Controllers\ScGooModelController;
use App\Http\Controllers\ScGooGradeController;
use App\Http\Controllers\MpmMakerModelController;

// sitemap用
use Illuminate\Http\Response;
use App\Models\ScGooMaker;
use App\Models\ScGooModel;
use App\Models\ScGooGrade;
use App\Http\Controllers\ScGooMileageController;
use App\Http\Controllers\ScGooYearController;

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

    $xml .= '<sitemap><loc>' . url('/sitemap-models.xml') . '</loc></sitemap>';
    $xml .= '<sitemap><loc>' . url('/sitemap-grades.xml') . '</loc></sitemap>';

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
