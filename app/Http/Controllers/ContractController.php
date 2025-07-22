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
    // Extraire les données de l'image base64
    $base64Image = $request->input('signature');
    $image = str_replace('data:image/png;base64,', '', $base64Image);
    $image = str_replace(' ', '+', $image);
    $imageName = 'signature_' . $contract->id . '_' . time() . '.png';

    // Stocker l’image dans storage/app/public/signatures
    Storage::disk('public')->put("signatures/{$imageName}", base64_decode($image));

    // Enregistrer le chemin dans la base
    $contract->signature_path = "signatures/{$imageName}";
    $contract->signed_at = now();
    $contract->save();

    return redirect()->route('process-payment', $contract->subscription_plan_id);
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
