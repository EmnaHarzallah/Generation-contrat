<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Request;
use App\Models\SubscriptionPlan;
use App\Http\Controllers\Auth;
use App\Http\Controllers\Contract;

abstract class SubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('subscription.index');
    }

    public function show($id)
    {
        $subscription = SubscriptionPlan::find($id);
        return view('subscription.show', compact('subscription'));
    }

    public function edit($id)
    {
        $subscription = SubscriptionPlan::find($id);
        return view('subscription.edit', compact('subscription'));
    }

    public function update(Request $request, $id)
    {
        $subscription = SubscriptionPlan::find($id);
        $subscription->update($request->all());
        return redirect()->route('subscription.show', $id);
    }

    public function destroy($id)
    {
        $subscription = SubscriptionPlan::find($id);
        $subscription->delete();
        return redirect()->route('subscription.index');
    }

    public function create()
    {
        return view('subscription.create');
    }

    public function store(Request $request)
    {
        $subscription = SubscriptionPlan::create($request->all());
        return redirect()->route('subscription.show', $subscription->id);
    }

    public function subscribe($planId)
    {
        $user = Auth::user();
        $plan = SubscriptionPlan::findOrFail($planId);

        // Générer le contrat
        $contract = Contract::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'title' => 'Contrat pour ' . $plan->name,
            'details' => 'Contrat généré pour l’abonnement ' . $plan->name,
            'start_date' => now(),
            'end_date' => now()->addDays($plan->duration),
        ]);

        return redirect()->route('/stripe' . $plan->id)->with('status', 'Contrat généré avec succès !');
    }


}
