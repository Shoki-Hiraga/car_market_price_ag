<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PyGooMakerController;
use App\Http\Controllers\InComeController;

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
Route::get('/', [PyGooMakerController::class, 'index'])->name('index');
