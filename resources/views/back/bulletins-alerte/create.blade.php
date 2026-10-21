@extends('back.layouts.back')
@section('title', 'Publier un bulletin de suivi')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <x-page-header title="Nouveau bulletin de suivi" subtitle="Mettre à jour la situation et faire évoluer le niveau de vigilance">
        @slot('actions')
            <a href="{{ route('admin.bulletins-alerte.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
        @endslot
    </x-page-header>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">Rédiger un bulletin de suivi</h5>
            <span class="badge bg-label-primary">Cycle de vie de l'alerte</span>
        </div>
        <div class="card-body pt-4">
            <form action="{{ route('admin.bulletins-alerte.store') }}" method="POST" novalidate>
                @csrf
                @include('back.bulletins-alerte._form')
            </form>
        </div>
    </div>
</div>
@endsection
