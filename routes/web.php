<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ContractController;
use App\Models\SubscriptionPlan;
use App\Models\Contract;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\PaymentController;
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
Route::any('showcontract/{plan}', [ContractController::class, 'showContract'])->name('contract.show');

// Show the signature form (GET)
Route::get('signcontract/{id}', [ContractController::class, 'showSignatureForm'])->name('show.signature');

// Handle the signature submission (POST)
Route::post('signcontract/{contract}', [ContractController::class, 'sign'])->name('contract.sign');

Route::get('/process-payment/{plan}', [PaymentController::class, 'showPaymentForm'])->name('show-payment-form');
Route::post('/process-payment/{plan}', [PaymentController::class, 'processPayment'])->name('process-payment');
Route::post('/stripe/webhook', [WebhookController::class, 'handleWebhook'])->name('stripe.webhook');

Route::get('/download-contract/{contract}', [ContractController::class, 'downloadContract'])->name('download.contract');