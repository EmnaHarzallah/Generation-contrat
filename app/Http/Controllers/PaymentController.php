<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Models\Contract;
class PaymentController extends Controller
{
    public function showPaymentForm($planId, $contractId)
    {
        $plan = SubscriptionPlan::findOrFail($planId);
        $contract = Contract::findOrFail($contractId);
        return view('payment.paymentform', compact('plan', 'contract'));
    }

    public function processPayment(Request $request, $planId, $contractId)
    {
        $plan = SubscriptionPlan::findOrFail($planId);
        $contract = Contract::findOrFail($contractId);

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

