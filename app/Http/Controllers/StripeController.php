<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Charge;

class StripeController extends Controller
{
    public function showForm($planId)
    {
        $plan = SubscriptionPlan::findOrFail($planId);
        return view('stripeform', compact('plan'));
    }

    public function processPayment(Request $request)
    {
        $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
        $planId = $request->input('plan_id');
        Stripe::setApiKey(config('services.stripe.secret'));

        // Retrieve the plan from the database
        $plan = SubscriptionPlan::findOrFail($planId);

        $charge = Charge::create([
            'amount' => $plan->price,
            'currency' => 'eur',
            'description' => 'Paiement test Laravel',
            'source' => $request->stripeToken,
        ]);


        return redirect()->route('showcontract', ['plan' => $planId]);
    }
}
