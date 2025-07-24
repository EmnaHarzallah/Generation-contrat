<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Models\Contract;
use App\Mail\ContractMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ContractController;

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
            'amount' => $plan->price * 100, 
            'currency' => 'eur',
            'automatic_payment_methods' => ['enabled' => true],
            'metadata' => [
                'plan_id' => $plan->id,
                'user_id' => auth()->id(),
            ],
        ]);
        
        return response()->json([
            'clientSecret' => $paymentIntent->client_secret,
        ]);         
    }

    public function sendContractEmail($user, $contractPath)
    {
        Mail::to($user->email)->send(new ContractMail($user, $contractPath));
    }
    public function finalizePayment(Request $request)
{
    $user = auth()->user();
    $plan = SubscriptionPlan::findOrFail($request->plan_id);
    $contractModel = new ContractController();
    $contractPath = $contractModel->generateContract($plan->id);
    $this->sendContractEmail($user, $contractPath);
    return response()->json(['status' => 'email_sent']);
}


}

