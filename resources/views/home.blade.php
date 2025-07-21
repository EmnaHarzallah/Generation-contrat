@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Choisissez votre abonnement</h2>
        <div class="row">
            @if($plans->count() > 0)
                @foreach($plans as $plan)
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">{{ $plan->name }}</h5>
                                <p class="card-text">{{ $plan->description }}</p>
                                <p class="card-text"><strong>Prix : </strong>{{ $plan->price }} € / {{ $plan->duration }} jours</p>
                                <form action="{{ route('contract.show', $plan->id) }}" method="GET">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">Choisir</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-md-12">
                    <div class="alert alert-info">Aucun abonnement disponible</div>
                </div>
            @endif
        </div>
    </div>
@endsection