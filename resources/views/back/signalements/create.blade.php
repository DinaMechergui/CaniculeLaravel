@extends('back.layouts.back')

@section('title', 'Nouveau signalement')

@section('content')
    <x-page-header title="Nouveau signalement" subtitle="Créer un signalement">
        @slot('actions')
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
                'action'           => route('admin.signalements.store'),
                'method'           => 'POST',
            ])
        </div>
    </div>
@endsection
