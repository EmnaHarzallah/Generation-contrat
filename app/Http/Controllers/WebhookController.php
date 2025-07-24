<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\ContractMail;

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
            \Log::error("User ou Plan introuvable. user_id: $userId, plan_id: $planId");
            return response()->json(['error' => 'User ou Plan introuvable'], 404);
        }

        try {
            $contractModel = new \App\Models\Contract();
            $contractModel->generateandSendContract($user, $plan);
        } catch (\Exception $e) {
            \Log::error("Erreur d'envoi de mail : " . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de l\'envoi du contrat.'], 500);
        }

        return response()->json(['status' => 'contract_sent']);
    }

    return response()->json(['status' => 'ignored']);
}

}
