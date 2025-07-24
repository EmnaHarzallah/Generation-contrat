<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ContractController;
use App\Models\SubscriptionPlan;
use App\Models\Contract;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

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

Route::get('/profile', function () {
    $user = Auth::user();
    return view('profile', compact('user'));
})->name('profile');


Route::get('/profile/edit', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');

// Show the contract and signature form
Route::any('showcontract/{plan}', [ContractController::class, 'showContract'])->name('contract.show');

// Show the signature form (GET)
Route::get('signcontract/{id}', [ContractController::class, 'showSignatureForm'])->name('show.signature');

// Handle the signature submission (POST)
Route::post('signcontract/{contract}', [ContractController::class, 'sign'])->name('contract.sign');

Route::get('/process-payment/{plan}/{contract}', [PaymentController::class, 'showPaymentForm'])->name('show-payment-form');
Route::post('/process-payment/{plan}/{contract}', [PaymentController::class, 'processPayment'])->name('process-payment');
Route::post('/stripe/webhook', [WebhookController::class, 'handleWebhook'])->name('stripe.webhook');

Route::get('/download-contract/{plan}', [ContractController::class, 'generateContract'])->name('download.contract');

Route::get('/test-gmail', function () {
    Mail::raw('Ceci est un test avec Gmail SMTP', function ($message) {
        $message->to(Auth::user()->email)
                ->subject('Test Gmail Laravel');
    });

    return 'Mail envoyé avec Gmail SMTP';
});

Route::get('/payment/success', function () {
    return view('payment.success');
})->name('payment.success')->middleware('auth');

Route::post('/payment/finalize', [PaymentController::class, 'finalizePayment'])->name('payment.finalize')->middleware('auth');
