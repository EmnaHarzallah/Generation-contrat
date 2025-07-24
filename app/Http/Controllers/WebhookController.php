<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\SubscriptionPlan;

class WebhookController extends Controller
{
    public function handleWebhook(Request $request)
{
    $payload = $request->all();
    $eventType = $payload['type'] ?? null;

    if ($eventType === 'payment_intent.succeeded') {
        $userId = $payload['data']['object']['metadata']['user_id'] ?? null;
        $planId = $payload['data']['object']['metadata']['plan_id'] ?? null;

        $user = \App\Models\User::find($userId);
        $plan = \App\Models\SubscriptionPlan::find($planId);

        if (!$user || !$plan) {
            \Log::error("User ou Plan introuvable.");
            return response()->json(['error' => 'User ou Plan introuvable'], 404);
        }

        // Génération du contrat Word
        $fileName = 'contrat_' . \Str::slug($user->name) . '_' . time() . '.docx';
        $contractPath = storage_path('app/public/contracts/' . $fileName);

        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor(storage_path('app/contract_template.docx'));
        $templateProcessor->setValue('name', $user->name);
        $templateProcessor->setValue('email', $user->email);
        $templateProcessor->setValue('plan_name', $plan->name);
        $templateProcessor->setValue('description', $plan->description);
        $templateProcessor->setValue('price', $plan->price);
        $templateProcessor->setValue('duration', $plan->duration);
        $templateProcessor->setValue('start_date', now()->format('d/m/Y'));
        $templateProcessor->setValue('end_date', now()->addDays($plan->duration)->format('d/m/Y'));
        $templateProcessor->setValue('contract_date', now()->format('d/m/Y'));

        $templateProcessor->saveAs($contractPath);

        // Envoi par email
        Mail::to($user->email)->send(new \App\Mail\ContractMail($user, $contractPath));
    }

    return response()->json(['status' => 'success']);
    }
}
