@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Signature du contrat</h2>

        <form id="signature-form" action="{{ route('contract.sign', $contract->id) }}" method="POST">
        @csrf

        <!-- Zone de signature -->
        <div class="mb-3">
            <label for="signature" class="form-label">Signez ci-dessous :</label>
            <canvas id="signature-pad" width="400" height="200" style="border:1px solid #000;"></canvas>
            <button type="button" id="clear-signature" class="btn btn-secondary mt-2">Effacer</button>
        </div>

        <!-- Signature en base64 -->
        <input type="hidden" name="signature" id="signature">

        <button type="submit" class="btn btn-primary mt-3">Confirmer</button>
        @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
    </form>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const canvas = document.getElementById('signature-pad');
        const signaturePad = new SignaturePad(canvas);

        // Effacer la signature
        document.getElementById('clear-signature').addEventListener('click', function () {
            signaturePad.clear();
        });

        // Avant envoi du formulaire
        document.getElementById('signature-form').addEventListener('submit', function (e) {
            console.log("submit");
            if (signaturePad.isEmpty()) {
                alert('Veuillez signer avant de soumettre.');
                e.preventDefault();
                return;
            }
            document.getElementById('signature').value = signaturePad.toDataURL(); // base64 image
        });
    });
</script>
@endsection