<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class PaymentController extends Controller
{
    public function showPaymentForm($planId)
    {
        $plan = SubscriptionPlan::findOrFail($planId);
        return view('payment.paymentform', compact('plan'));
    }

    public function processPayment(Request $request, $planId)
    {
        $plan = SubscriptionPlan::findOrFail($planId);

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $plan->price * 100, // en centimes
            'currency' => 'eur',
            'metadata' => [
                'plan_id' => $plan->id,
                'user_id' => auth()->id(),
            ],
        ]);

        return response()->json([
            'clientSecret' => $paymentIntent->client_secret,
        ]);
    }
}

