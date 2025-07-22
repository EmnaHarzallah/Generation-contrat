@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Mon Profil</h3>
        </div>
        <div class="card-body">
            <p><strong>Nom :</strong> {{ $user->name }}</p>
            <p><strong>Email :</strong> {{ $user->email }}</p>
            <p><strong>Date d'inscription :</strong> {{ $user->created_at->format('d/m/Y') }}</p>
            <p><strong>Dernière connexion :</strong>
                {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y') : 'Jamais connecté' }}
            </p>
            <hr>
            <h5 class="mt-4">Mes contrats</h5>
            <ul class="list-group">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Total
                    <span class="badge bg-secondary">{{ $user->contracts->count() }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    En cours
                    <span class="badge bg-warning text-dark">{{ $user->contracts->where('status', 'active')->count() }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Terminés
                    <span class="badge bg-success">{{ $user->contracts->where('status', 'terminated')->count() }}</span>
                </li>
            </ul>

            <div class="mt-4 text-end">
                <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary">Modifier le profil</a>
            </div>
        </div>
    </div>
</div>
@endsection
