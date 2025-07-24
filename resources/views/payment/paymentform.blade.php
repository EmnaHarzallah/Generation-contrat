@extends('layouts.app')

@section('content')
<head><script src="https://js.stripe.com/v3/"></script></head>
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

@section('scripts')
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

        try {
            // 1. Appelle Laravel pour créer le PaymentIntent
            const response = await fetch("{{ route('process-payment', ['plan' => $plan->id, 'contract' => $contract->id]) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({})
            });

            const data = await response.json();

            if (!data.clientSecret) {
                throw new Error('Client secret manquant');
            }

            // 2. Confirmer le paiement avec Stripe
            const result = await stripe.confirmCardPayment(data.clientSecret, {
                payment_method: {
                    card: cardElement
                }
            });

            if (result.error) {
                paymentMessage.textContent = 'Erreur de paiement : ' + result.error.message;
                paymentMessage.classList.add('text-danger');
            } else if (result.paymentIntent && result.paymentIntent.status === 'succeeded') {
                // 3. Envoie une requête à Laravel pour générer & envoyer le contrat
                const finalizeResponse = await fetch("{{ route('payment.finalize') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ plan_id: {{ $plan->id }} })
                });

                const finalizeData = await finalizeResponse.json();

                if (finalizeData.status === 'email_sent') {
                    window.location.href = "{{ route('payment.success') }}";
                } else {
                    paymentMessage.textContent = "Paiement ok, mais une erreur est survenue lors de l'envoi du contrat.";
                    paymentMessage.classList.add('text-danger');
                }
            }
        } catch (error) {
            paymentMessage.textContent = "Erreur : " + error.message;
            paymentMessage.classList.add('text-danger');
        }

        document.getElementById('loading').style.display = 'none';
        document.getElementById('pay-btn').disabled = false;
        document.getElementById('pay-btn').textContent = 'Payer';
    });
</script>
@endsection

@endsection