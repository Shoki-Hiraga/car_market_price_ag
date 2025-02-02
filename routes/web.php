<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScGooMakerController;
use App\Http\Controllers\ScGooModelController;
use App\Http\Controllers\ScGooGradeController;

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
// メーカー一覧を表示するルート
Route::get('/', [ScGooMakerController::class, 'index'])->name('maker.index');
// モデル一覧を表示するルート
Route::get('/model', [ScGooModelController::class, 'index'])->name('model.index');
// グレード一覧を表示するルート
Route::get('/grade', [ScGooGradeController::class, 'index'])->name('grade.index');