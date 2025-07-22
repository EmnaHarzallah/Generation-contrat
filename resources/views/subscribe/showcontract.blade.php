@extends('layouts.app')

@section('content')
<div class="container">
    <div class="contract-content">
        {!! $contractHtml !!}
    </div>

    <form method="GET" action="{{ route('show.signature', $contract->id) }}">
        @csrf
        <div class="mb-3">
            <label for="remarques" class="form-label">Si vous avez des remarques, veuillez les écrire ici</label>
            <textarea id="remarques" name="remarques" class="form-control" rows="4" placeholder="Avez vous des remarques ?"></textarea>
        </div>
            <form action="{{ route('show.signature', $contract->id ) }}" method="GET">
            @csrf
            <button type="submit" class="btn btn-primary">
                Valider
            </button>
        </form>
        
    </form>
</div>
@endsection


