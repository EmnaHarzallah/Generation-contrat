@extends('layouts.app')

@section('content')
<div class="container">
    <div class="contract-content">
        {!! $contractHtml !!}
    </div>

    <form method="POST" action="{{ route('contract.sign', $plan->id) }}">
        @csrf
        <div class="mb-3">
            <label for="signature" class="form-label">Votre signature</label>
            <textarea id="remarques" name="remarques" class="form-control" rows="4" placeholder="Avez vous des remarques ?"></textarea>
        </div>
        <form action="{{ route('show.signature', $$contractHtml->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary">
                Signer le contrat
            </button>
        </form>
        
    </form>
</div>
@endsection


