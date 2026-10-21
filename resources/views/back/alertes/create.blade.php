@extends('back.layouts.back')
@section('title', 'Créer une alerte')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <x-page-header title="Nouvelle alerte" subtitle="Déclarer un nouvel événement à risque pour la population">
        @slot('actions')
            <a href="{{ route('admin.alertes.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
        @endslot
    </x-page-header>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">Informations de l'alerte</h5>
            <span class="badge bg-label-primary">Module Alertes</span>
        </div>
        <div class="card-body pt-4">
            <form action="{{ route('admin.alertes.store') }}" method="POST" novalidate>
                @csrf
                @include('back.alertes._form')
            </form>
        </div>
    </div>
</div>
@endsection
