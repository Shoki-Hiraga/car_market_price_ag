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
use App\Http\Controllers\Sitemap\SitemapIndexController;
use App\Http\Controllers\Sitemap\ModelSitemapController;
use App\Http\Controllers\Sitemap\GradeSitemapController;
use App\Http\Controllers\Sitemap\MileageSitemapController;
use App\Http\Controllers\Sitemap\YearSitemapController;

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

// サイトマップ
// sitemap.xml（メインインデックス）
Route::get('/sitemap.xml', [SitemapIndexController::class, 'index'])->name('sitemap.xml');

// sitemap/以下に各タイプ
Route::get('/sitemap-model/{page?}', [ModelSitemapController::class, 'index'])->name('sitemap.model');
Route::get('/sitemap-grade/{page?}', [GradeSitemapController::class, 'index'])->name('sitemap.grade');
Route::get('/sitemap-mileage/{page?}', [MileageSitemapController::class, 'index'])->name('sitemap.mileage');
Route::get('/sitemap-year/{page?}', [YearSitemapController::class, 'index'])->name('sitemap.year');
