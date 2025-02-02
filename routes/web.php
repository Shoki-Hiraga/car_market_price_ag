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
Route::get('/', [ScGooMakerController::class, 'index'])->name('index');
Route::get('/model', [ScGooModelController::class, 'index'])->name('index');
Route::get('/grade', [ScGooGradeController::class, 'index'])->name('index');