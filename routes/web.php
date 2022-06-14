<?php

use Illuminate\Support\Facades\Route;
use App\Http\Livewire\shoppingcart;
use App\Http\Controllers\PaypalController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

require __DIR__.'/auth.php';

Route::get('/shoppingcart',Shoppingcart::class)->name('shoppingcart');

Route::get('payment-cancel',[PaypalController::class,'cancel'])
    ->name('payment.cancel');
Route::get('payment-success',[PaypalController::class,'success'])
    ->name('payment.success');
