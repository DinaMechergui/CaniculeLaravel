@extends('back.layouts.back')

@section('title', 'Créer une mesure de prévention')

@section('content')
    <x-page-header title="Nouvelle mesure de prévention" subtitle="Formulaire d'enregistrement d'une mesure d'urgence">
        @slot('actions')
            <a href="{{ route('admin.mesures-prevention.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
        @endslot
    </x-page-header>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.mesures-prevention.store') }}">
                @include('back.mesures-prevention._form', ['isEdit' => false])
            </form>
        </div>
    </div>
@endsection
