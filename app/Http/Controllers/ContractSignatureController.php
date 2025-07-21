<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Contract;

class ContractSignatureController extends Controller
{
    public function sendToSign($contractId)
    {
        $contract = Contract::findOrFail($contractId);
        $user = $contract->user;

        $apiKey = env('YOUSIGN_API_KEY');

        // 1. Upload du fichier à signer
        $response = Http::withToken($apiKey)->attach(
            'file', file_get_contents(storage_path('app/contracts/generated/contract_' . $user->id . '.pdf')), 'contract.pdf'
        )->post('https://api.yousign.app/v3/files');

        $fileId = $response->json()['id'];

        // 2. Créer le processus de signature (signature request)
        $signatureRequest = Http::withToken($apiKey)->post('https://api.yousign.app/v3/signature_requests', [
            'name' => 'Signature Contrat - ' . $user->name,
            'files' => [
                [
                    'id' => $fileId,
                    'display_name' => 'Contrat à signer',
                ]
            ],
            'signers' => [
                [
                    'info' => [
                        'first_name' => $user->name,
                        'email' => $user->email,
                    ],
                    'signature_level' => 'electronic_signature',
                    'authentication_mode' => 'email',
                ]
            ],
            'signature_type' => 'embedded',
        ]);

        $requestData = $signatureRequest->json();
        return redirect()->to($requestData['signers'][0]['signature_links'][0]['url']);
    }
}
