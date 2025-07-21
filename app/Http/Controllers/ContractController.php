<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;
use App\Models\Contract;
use App\Models\User;
use App\Models\SubscriptionPlan;
use Carbon\Carbon;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ContractController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('contract.index');
    }
    public function up()
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('signature_path')->nullable();
        });
    }
    public function show($id)
    {
        $contract = Contract::find($id);
        return view('contract.show', compact('contract'));
    }

    public function edit($id)
    {
        $contract = Contract::find($id);
        return view('contract.edit', compact('contract'));
    }

    public function update(Request $request, $id)
    {
        $contract = Contract::find($id);
        $contract->update($request->all());
        return redirect()->route('contract.show', $id);
    }

    public function destroy($id)
    {
        $contract = Contract::find($id);
        $contract->delete();
        return redirect()->route('contract.index');
    }


    public function showContract($planId)
{
    $user = Auth::user();
    $plan = SubscriptionPlan::findOrFail($planId);

  
    $contractHtml = "
        <h2>Contrat pour {$user->name}</h2>
        <p>Email: {$user->email}</p>
        <p>Description du plan: {$plan->description}</p>
        <p>Début: " . date('d/m/Y') . "</p>
        <p>Fin: " . now()->addDays($plan->duration)->format('d/m/Y') . "</p>
        <p>Prix: {$plan->price} €</p>
        <p>Durée: {$plan->duration} jours</p>
        <hr>
        <p>Veuillez signer ci-dessous :</p>
    ";

    return view('subscribe.showcontract', compact('contractHtml', 'plan'));
}





    public function sign(Request $request, $id)
{
    $request->validate([
        'signature' => 'required|string',
    ]);

    $contract = Contract::findOrFail($id);

    // Extraire les données de l'image base64
    $base64Image = $request->input('signature');
    $image = str_replace('data:image/png;base64,', '', $base64Image);
    $image = str_replace(' ', '+', $image);
    $imageName = 'signature_' . $contract->id . '_' . time() . '.png';

    // Stocker l’image dans storage/app/public/signatures
    Storage::disk('public')->put("signatures/{$imageName}", base64_decode($image));

    // Enregistrer le chemin dans la base (si vous avez un champ "signature_path")
    $contract->signature_path = "signatures/{$imageName}";
    $contract->signed_at = now();
    $contract->save();

    return view('signature', compact('contract'));
}
/*public function createAndSendToYousign($planId)
{
    $user = Auth::user();
    $plan = SubscriptionPlan::findOrFail($planId);

    // 1. Générer le contrat Word
    $templatePath = storage_path('contract_template.docx');
    $docxPath = storage_path('app/public/contrat_' . $user->id . '.docx');

    $templateProcessor = new TemplateProcessor($templatePath);
    $templateProcessor->setValue('name', $user->name);
    $templateProcessor->setValue('email', $user->email);
    $templateProcessor->setValue('description', $plan->description);
    $templateProcessor->setValue('start_date', now()->format('d/m/Y'));
    $templateProcessor->setValue('end_date', now()->addDays($plan->duration)->format('d/m/Y'));
    $templateProcessor->setValue('price', $plan->price);
    $templateProcessor->setValue('duration', $plan->duration);
    $templateProcessor->setValue('contract_date', now()->format('d/m/Y'));
    $templateProcessor->saveAs($docxPath);

    // 2. Convertir en PDF (nécessite LibreOffice installé)
    $pdfPath = storage_path('app/public/contrat_' . $user->id . '.pdf');
    exec("libreoffice --headless --convert-to pdf --outdir " . escapeshellarg(dirname($pdfPath)) . ' ' . escapeshellarg($docxPath));

    if (!File::exists($pdfPath)) {
        return back()->with('error', 'Échec de la conversion en PDF.');
    }

    // 3. Créer un contrat en base de données
    $contract = new Contract();
    $contract->user_id = $user->id;
    $contract->subscription_plan_id = $plan->id;
    $contract->signature_path = null;
    $contract->signed_at = null;
    $contract->save();

    // 4. Envoyer à Yousign
    $apiKey = env('YOUSIGN_API_KEY');

    $response = Http::withToken($apiKey)->attach(
        'file', file_get_contents($pdfPath), 'contrat.pdf'
    )->post('https://api.yousign.app/v3/files');

    if (!$response->successful()) {
        return back()->with('error', 'Erreur lors de l’envoi du fichier à Yousign.');
    }

    $fileId = $response->json()['id'];

    $signatureRequest = Http::withToken($apiKey)->post('https://api.yousign.app/v3/signature_requests', [
        'name' => 'Signature contrat ' . $user->name,
        'files' => [
            ['id' => $fileId]
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

    if (!$signatureRequest->successful()) {
        return back()->with('error', 'Erreur lors de la création de la signature.');
    }

    $url = $signatureRequest->json()['signers'][0]['signature_links'][0]['url'];

    return redirect()->to($url); // Redirige vers l’interface de signature Yousign
}
*/

}
