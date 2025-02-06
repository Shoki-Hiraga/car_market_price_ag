<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScGooMakerController;
use App\Http\Controllers\ScGooModelController;
use App\Http\Controllers\ScGooGradeController;
use App\Http\Controllers\MarketPriceMasterController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('main.index');
});
// ホーム
Route::get('/', [ScGooMakerController::class, 'index'])->name('maker.index');

// モデル一覧
Route::get('/model', [ScGooModelController::class, 'index'])->name('model.index');

// モデル詳細
Route::get('/model/{id}', [ScGooModelController::class, 'show'])->name('model.detail');

// グレード詳細（モデルの下の階層にする）
Route::get('/model/{model_id}/grade/{grade_id}', [ScGooGradeController::class, 'show'])->name('grade.detail');
