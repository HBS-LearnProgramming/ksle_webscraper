<?php

use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebScrapKlseControler;
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
    return redirect()->route('webscrap.form');
});
Route::get('/', [WebScrapKlseControler::class, 'index'])->name('webscrap.form');
Route::post('/', [WebScrapKlseControler::class, 'formSubmit']);
Route::prefix('stock')->group(function () {
    Route::get('/', [StockController::class, 'index'])->name('stock.form');
    Route::post('/', [StockController::class, 'formSubmit']);
    Route::post('/{id}/delete', [StockController::class, 'delete']);
    Route::get('/{id}/edit', [StockController::class, 'index']);
});



