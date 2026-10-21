@extends('back.layouts.back')
@section('title', 'Modifier l\'alerte : ' . $alerte->titre)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <x-page-header title="Modifier l'alerte" :subtitle="'Mise à jour de : ' . $alerte->titre">
        @slot('actions')
            <a href="{{ route('admin.alertes.show', $alerte) }}" class="btn btn-outline-primary me-2">
                <i class="bx bx-show me-1"></i> Fiche détaillée
            </a>
            <a href="{{ route('admin.alertes.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
        @endslot
    </x-page-header>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">Modifier les paramètres généraux de l'alerte</h5>
            <span class="badge bg-label-info">Alerte #{{ $alerte->id }}</span>
        </div>
        <div class="card-body pt-4">
            <form action="{{ route('admin.alertes.update', $alerte) }}" method="POST">
                @csrf
                @method('PUT')
                @include('back.alertes._form')
            </form>
        </div>
    </div>
</div>
@endsection
