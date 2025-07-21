@extends('layouts.app')

@section('content')

    <head>
        <meta charset="UTF-8">
        <title>Paiement Stripe</title>
        <script src="https://js.stripe.com/v3/"></script>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: #f6f9fc;
                display: flex;
                justify-content: center;
                padding-top: 50px;
            }

            .container {
                background: white;
                padding: 30px;
                border-radius: 12px;
                box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
                width: 100%;
                max-width: 500px;
            }

            h2 {
                margin-bottom: 10px;
            }

            #card-element {
                padding: 10px;
                border: 1px solid #ccc;
                border-radius: 8px;
                margin-bottom: 20px;
            }

            #submit {
                background-color: #6772e5;
                color: white;
                border: none;
                padding: 10px 16px;
                border-radius: 6px;
                cursor: pointer;
                font-size: 16px;
            }

            #submit:disabled {
                background-color: #a0a0a0;
            }

            .error {
                color: red;
                margin-top: 10px;
            }

            .spinner {
                display: inline-block;
                height: 16px;
                width: 16px;
                border: 2px solid white;
                border-top: 2px solid transparent;
                border-radius: 50%;
                animation: spin 0.8s linear infinite;
            }

            @keyframes spin {
                to {
                    transform: rotate(360deg);
                }
            }
        </style>
    </head>

    <body>
        <div class="container">
            <h2>Paiement pour : {{ $plan->name }}</h2>
            <p>Montant : {{ $plan->price }} €</p>

            <form id="payment-form">
                @csrf
                <input type="hidden" id="plan_id" value="{{ $plan->id }}">
                <div id="card-element"></div>
                <button id="submit">
                    <span id="button-text">Payer</span>
                    <span id="spinner" class="spinner" style="display: none;"></span>
                </button>
                <div id="payment-message" class="error" role="alert"></div>
            </form>
        </div>

        <script>
            const stripe = Stripe("{{ config('services.stripe.key') }}");
            const elements = stripe.elements();
            const card = elements.create('card', {
                style: {
                    base: {
                        fontSize: '16px',
                        color: '#32325d',
                        '::placeholder': {
                            color: '#aab7c4',
                        },
                    },
                    invalid: {
                        color: '#fa755a',
                    },
                }
            });
            card.mount('#card-element');

            const form = document.getElementById('payment-form');
            const submitBtn = document.getElementById('submit');
            const spinner = document.getElementById('spinner');
            const buttonText = document.getElementById('button-text');
            const messageDiv = document.getElementById('payment-message');
            const planId = document.getElementById('plan_id').value;

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                submitBtn.disabled = true;
                spinner.style.display = 'inline-block';
                buttonText.textContent = 'Traitement...';
                messageDiv.textContent = '';

                try {
                    const response = await fetch("{{ route('stripe.process', $plan) }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        },
                        body: JSON.stringify({ plan_id: planId })
                    });

                    const data = await response.json();

                    if (data.error) {
                        throw new Error(data.error);
                    }

                    const result = await stripe.confirmCardPayment(data.clientSecret, {
                        payment_method: {
                            card: card,
                        }
                    });

                    if (result.error) {
                        throw new Error(result.error.message);
                    }

                    if (result.paymentIntent.status === 'succeeded') {
                        window.location.href = "/showcontract?plan=" + planId;
                    }
                } catch (error) {
                    messageDiv.textContent = error.message;
                    submitBtn.disabled = false;
                    spinner.style.display = 'none';
                    buttonText.textContent = 'Payer';
                }
            });
        </script>
    </body>
@endsection