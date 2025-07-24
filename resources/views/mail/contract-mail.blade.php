@component('mail::message')
# Bonjour {{ $user->name }},

Merci pour votre paiement.  
Vous trouverez ci-joint votre contrat pour le plan **{{ $user->subscriptionPlan->name ?? 'souscrit' }}**.

@component('mail::button', ['url' => config('app.url')])
Accéder à votre espace
@endcomponent

Merci,<br>
{{ config('app.name') }}
@endcomponent
