<?php

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
Route::get('/webScrapKlse', [WebScrapKlseControler::class, 'index'])->name('webscrap.form');
Route::post('/webScrapKlse', [WebScrapKlseControler::class, 'formSubmit']);
