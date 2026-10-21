@extends('back.layouts.back')

@section('title', 'Modifier : ' . $typeSignalement->libelle)

@section('content')
    <x-page-header :title="'Modifier : ' . $typeSignalement->libelle" subtitle="Modification d'un type de signalement">
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
                'action'          => route('admin.types-signalement.update', $typeSignalement),
                'method'          => 'PUT',
            ])
        </div>
    </div>
@endsection
