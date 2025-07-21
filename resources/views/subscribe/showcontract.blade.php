@extends('layouts.app')

@section('content')
<div class="container">
    <div class="contract-content">
        {!! $contractHtml !!}
    </div>

    <form method="GET" action="{{ route('show.signature', $plan->id) }}">
        @csrf
        <div class="mb-3">
            <label for="signature" class="form-label">Votre signature</label>
            <textarea id="remarques" name="remarques" class="form-control" rows="4" placeholder="Avez vous des remarques ?"></textarea>
        </div>
            <form action="{{ route('show.signature', $plan->id) }}" method="GET">
            @csrf
            <button type="submit" class="btn btn-primary">
                Signer le contrat
            </button>
        </form>
        
    </form>
</div>
@endsection


