@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="d-flex justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="card shadow p-4" style="max-width: 400px;">
        <h3 class="mb-3 text-center">Paiement pour : <span class="text-primary">{{ $plan->name }}</span></h3>
        <p class="fs-5 text-center mb-4">Montant : <strong>{{ $plan->price }} €</strong></p>
        <form id="payment-form" action="{{ route('process-payment', ['plan' => $plan->id, 'contract' => $contract->id]) }}" method="POST">
            @csrf
            <div id="card-element" class="mb-3"></div>
            <button type="submit" id="pay-btn" class="btn btn-primary w-100">Payer</button>
            <span id="loading" style="display:none;"></span>
        </form>
        <div id="payment-message" class="mt-3 text-center"></div>
            <a id="retour_page" href="{{ route('dashboard') }}" target="_blank" style="display:none;">Retour à la page d'accueil</a>
    </div>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script>
    const stripe = Stripe('{{ config('services.stripe.key') }}');
    const elements = stripe.elements();
    const cardElement = elements.create('card');
    cardElement.mount('#card-element');

    const form = document.getElementById('payment-form');
    const paymentMessage = document.getElementById('payment-message');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        document.getElementById('loading').style.display = 'inline';
        document.getElementById('pay-btn').disabled = true;
        document.getElementById('pay-btn').textContent = 'En cours de traitement...';
        
        const response = await fetch("{{ route('process-payment', ['plan' => $plan->id, 'contract' => $contract->id]) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({})
        });

        const data = await response.json();

        if (data.clientSecret) {
            const result = await stripe.confirmCardPayment(data.clientSecret, {
                payment_method: {
                    card: cardElement,
                }
            });

            if (result.error) {
                paymentMessage.textContent = 'Erreur de paiement : ' + result.error.message;
                paymentMessage.classList.add('text-danger');
            } else if (result.paymentIntent && result.paymentIntent.status === 'succeeded') {
                paymentMessage.textContent = 'Paiement réussi ! Votre contrat sera envoyé à votre adresse email.';
                paymentMessage.classList.remove('text-danger');
                paymentMessage.classList.add('text-success');   
                document.getElementById('retour_page').style.display = 'block';
        
                document.getElementById('retour_page').href = '{{ route('dashboard') }}';                             
            }
        } else {
            paymentMessage.textContent = 'Erreur lors de la création du paiement.';
            paymentMessage.classList.add('text-danger');
        }
        
       
        

    });
</script>
@endsection