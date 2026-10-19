@extends('back.layouts.back')

@section('title', 'Modifier le signalement')

@section('content')
    <x-page-header title="Modifier le signalement" subtitle="Modification d'un signalement">
        @slot('actions')
            <a href="{{ route('admin.signalements.show', $signalement) }}" class="btn btn-outline-secondary">
                <i class="bx bx-show me-1"></i> Voir
            </a>
            <a href="{{ route('admin.signalements.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
        @endslot
    </x-page-header>

    <div class="card">
        <div class="card-body">
            @include('back.signalements._form', [
                'signalement'      => $signalement,
                'typesSignalement' => $typesSignalement,
                'statuts'          => $statuts,
                'action'           => route('admin.signalements.update', $signalement),
                'method'           => 'PUT',
            ])
        </div>
    </div>
@endsection
