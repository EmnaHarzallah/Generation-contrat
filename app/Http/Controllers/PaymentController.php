<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Models\Contract;
use App\Mail\ContractMail;
use Illuminate\Support\Facades\Mail;

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
            'automatic_payment_methods' => ['enabled' => true], // ✅ important
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

    // Génère le contrat (utilise une méthode du modèle Contract ou écris-la ici)
    $fileName = 'contrat_' . \Str::slug($user->name) . '_' . time() . '.docx';
    $contractPath = storage_path('app/public/contracts/' . $fileName);
    $templatePath = storage_path('app/contract_template.docx');
    $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);
    $signature = \Storage::disk('public')->get('signatures/signature_' . $plan->id . '_' . $user->name . '.png');
    
    $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor(storage_path('app/contract_template.docx'));
    $templateProcessor->setValue('name', $user->name);
    $templateProcessor->setValue('email', $user->email);
    $templateProcessor->setValue('plan_name', $plan->name);
    $templateProcessor->setValue('description', $plan->description);
    $templateProcessor->setValue('price', $plan->price);
    $templateProcessor->setValue('duration', $plan->duration);
    $end_date = \Carbon\Carbon::now()->addDays($plan->duration);
    $templateProcessor->setValue('end_date', $end_date->format('d/m/Y'));
    $templateProcessor->setValue('start_date', \Carbon\Carbon::now()->format('d/m/Y'));
    $templateProcessor->setValue('contract_date', \Carbon\Carbon::now()->format('d/m/Y'));
    $templateProcessor->setImageValue('Signature', [
        'path' => storage_path('app/public/signatures/signature_' . $plan->id . '_' . $user->name . '.png'),
        'width' => 200, // largeur en px
        'height' => 80, // hauteur en px
        'ratio' => false
    ]);

    $templateProcessor->saveAs($contractPath);

    // Envoi de l'email via ta méthode existante
    $this->sendContractEmail($user, $contractPath);

    return response()->json(['status' => 'email_sent']);
}


}

