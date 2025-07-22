<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ContractController;
use App\Models\SubscriptionPlan;
use App\Models\Contract;
use App\Http\Controllers\StripeController;

use Illuminate\Support\Facades\Auth;

Auth::routes();

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/logout', function () {
    return view('auth.logout');
});
Route::get('/register', function () {
    return view('auth.register');
})->name('register');


Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/home', function () {
    $plans = SubscriptionPlan::all();
    return view('home', compact('plans'));
})->name('home');

Route::get('/dashboard', function () {
    $plans = SubscriptionPlan::all();
    return view('home', compact('plans'));
})->name('dashboard');



// Show the contract and signature form
Route::any('showcontract/{id}', [ContractController::class, 'showContract'])->name('contract.show');

// Show the signature form (GET)
Route::get('signcontract/{id}', [ContractController::class, 'showSignatureForm'])->name('show.signature');

// Handle the signature submission (POST)
Route::post('signcontract/{id}', [ContractController::class, 'sign'])->name('contract.sign');

// Handle the signature submission

Route::get('/stripe/{plan}', [StripeController::class, 'showForm'])->name('stripe.form');
Route::post('/stripe/{plan}', [StripeController::class, 'processPayment'])->name('stripe.process');
//Route::get('/contracts/{id}/sign-digital', [ContractController::class, 'sendToYousign'])->name('contracts.sendToYousign');

