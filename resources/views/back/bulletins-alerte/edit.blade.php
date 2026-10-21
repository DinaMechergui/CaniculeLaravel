@extends('back.layouts.back')
@section('title', 'Modifier le bulletin #' . $bulletinAlerte->id)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <x-page-header title="Modifier le bulletin" :subtitle="'Bulletin #' . $bulletinAlerte->id . ' — Alerte : ' . $bulletinAlerte->alerte->titre">
        @slot('actions')
            <a href="{{ route('admin.bulletins-alerte.show', $bulletinAlerte) }}" class="btn btn-outline-primary me-2">
                <i class="bx bx-show me-1"></i> Fiche détaillée
            </a>
            <a href="{{ route('admin.bulletins-alerte.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
        @endslot
    </x-page-header>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">Modifier les informations du bulletin</h5>
            <span class="badge {{ $bulletinAlerte->niveau_badge_class }}">
                {{ $bulletinAlerte->niveau_label }}
            </span>
        </div>
        <div class="card-body pt-4">
            <form action="{{ route('admin.bulletins-alerte.update', $bulletinAlerte) }}" method="POST" novalidate>
                @csrf
                @method('PUT')
                @include('back.bulletins-alerte._form')
            </form>
        </div>
    </div>
</div>
@endsection
