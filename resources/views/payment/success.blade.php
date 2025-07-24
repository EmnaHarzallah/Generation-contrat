@extends('layouts.app')

@section('content')
<div class="container mt-5 text-center">
    <div class="alert alert-success">
        <h2 class="mb-3">✅ Paiement réussi !</h2>
        <p>Merci pour votre souscription, {{ Auth::user()->name }}.</p>
        <p>Un email contenant votre contrat a été envoyé à <strong>{{ Auth::user()->email }}</strong>.</p>

        <a href="{{ route('dashboard') }}" class="btn btn-primary mt-4">Retour au tableau de bord</a>
    </div>
</div>
@endsection
