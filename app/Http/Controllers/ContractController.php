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
use Illuminate\Support\Facades\Mail;
use App\Mail\ContractMail;

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

    public function generateContract($planId)
{
    $user = auth()->user();
    $plan = SubscriptionPlan::findOrFail($planId);

    // Charger le template Word
    $templatePath = storage_path('app/contract_template.docx');
    $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);
    $signature = \Storage::disk('public')->get('signatures/signature_' . $plan->id . '_' . $user->name . '.png');

    // Remplacer les variables du template
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

    $contractPath = storage_path('app/public/contracts/contract_' . $user->id . '.docx');
    $templateProcessor->saveAs($contractPath);
    return $contractPath;
}



    public function showContract($planId)
{
    $user = Auth::user();
    $plan = SubscriptionPlan::findOrFail($planId);
    $contract = new Contract();
    $contract->user_id = Auth::user()->id;
    $contract->details = $plan->description;
    $contract->start_date = now();
    $contract->end_date = now()->addDays($plan->duration);
    $contract->subscription_plan_id = $plan->id;
    $contract->title = $plan->name; 
    $contract->signature_path = null;
    $contract->signed_at = null;
    $contract->updated_at = now();
    $contract->created_at = now();
    $contract->save();

  
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

    return view('subscribe.showcontract', compact('contractHtml', 'plan' , 'contract'));
}


public function showSignatureForm($contract)
{
    $contract = Contract::findOrFail($contract);
    $plan = SubscriptionPlan::findOrFail($contract->subscription_plan_id);
    return view('signature', compact('contract' , 'plan'));
}


public function sign(Request $request, $contract)
{
    $request->validate([
        'signature' => 'required|string',
    ]);
    $contract = Contract::findOrFail($contract);
    $plan = SubscriptionPlan::findOrFail($contract->subscription_plan_id);
    $user = Auth::user();
    // Extraire les données de l'image base64
    $base64Image = $request->input('signature');
    $image = str_replace('data:image/png;base64,', '', $base64Image);
    $image = str_replace(' ', '+', $image);
    $imageName = 'signature_' . $plan->id . '_' . $user->name . '.png';

    // Stocker l’image dans storage/app/public/signatures
    Storage::disk('public')->put("signatures/{$imageName}", base64_decode($image));

    // Enregistrer le chemin dans la base
    $contract->signature_path = "signatures/{$imageName}";
    $contract->signed_at = now();
    $contract->save();

    return redirect()->route('show-payment-form', [
        'plan' => $contract->subscription_plan_id,
        'contract' => $contract->id
    ]);
}

public function downloadContract($contractId)
{
    $contract = Contract::findOrFail($contractId);
    $filePath = storage_path('C:\Users\ADMIN\NewCA\storage\contract_template.docx'); 

    if (!file_exists($filePath)) {
        abort(404, 'Contrat non trouvé.');
    }

    return response()->download($filePath, 'contrat_' . $contract->id . '.pdf');
}


}
