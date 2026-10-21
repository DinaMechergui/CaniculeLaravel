@extends('back.layouts.back')

@section('title', 'Nouveau type de signalement')

@section('content')
    <x-page-header title="Nouveau type de signalement" subtitle="Créer un type de signalement">
        @slot('actions')
            <a href="{{ route('admin.types-signalement.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
        @endslot
    </x-page-header>

    <div class="card">
        <div class="card-body">
            @include('back.types-signalement._form', [
                'typeSignalement' => $typeSignalement,
                'action'          => route('admin.types-signalement.store'),
                'method'          => 'POST',
            ])
        </div>
    </div>
@endsection
