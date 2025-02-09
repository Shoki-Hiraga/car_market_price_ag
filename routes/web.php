<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScGooMakerController;
use App\Http\Controllers\ScGooModelController;
use App\Http\Controllers\ScGooGradeController;

// sitemap用
use Illuminate\Http\Response;
use App\Models\ScGooMaker;
use App\Models\ScGooModel;
use App\Models\ScGooGrade;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Webサイトのルート定義
|
*/

// ホーム
Route::get('/', [ScGooMakerController::class, 'index'])->name('maker.index');

// モデル一覧
Route::get('/model', [ScGooModelController::class, 'index'])->name('model.index');

// モデル詳細
Route::get('/model/{id}', [ScGooModelController::class, 'show'])->name('model.detail');

// グレード詳細
Route::get('/model/{model_id}/grade/{grade_id}', [ScGooGradeController::class, 'show'])->name('grade.detail');


// sitemap.xmlの動的生成
Route::get('/sitemap.xml', function () {
    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    // ホームページ
    $xml .= '<url>';
    $xml .= '<loc>' . url(route('maker.index')) . '</loc>';
    $xml .= '<changefreq>daily</changefreq>';
    $xml .= '<priority>1.0</priority>';
    $xml .= '</url>';

    // モデル一覧ページ
    $xml .= '<url>';
    $xml .= '<loc>' . url(route('model.index')) . '</loc>';
    $xml .= '<changefreq>weekly</changefreq>';
    $xml .= '<priority>0.8</priority>';
    $xml .= '</url>';

    // モデル詳細ページ
    $models = ScGooModel::latest()->get();
    foreach ($models as $model) {
        $xml .= '<url>';
        $xml .= '<loc>' . url(route('model.detail', ['id' => $model->id])) . '</loc>';
        $xml .= '<lastmod>' . $model->updated_at->toW3cString() . '</lastmod>';
        $xml .= '<changefreq>weekly</changefreq>';
        $xml .= '<priority>0.7</priority>';
        $xml .= '</url>';
    }

    // グレード詳細ページ（model_name_id を使用）
    $grades = ScGooGrade::whereNotNull('model_name_id')->latest()->get();
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
