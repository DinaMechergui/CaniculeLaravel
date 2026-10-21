@extends('back.layouts.back')

@section('title', 'Nouvelle catégorie de conseils')

@section('content')
    <x-page-header title="Nouvelle catégorie" subtitle="Créer une catégorie de conseils">
        @slot('actions')
            <a href="{{ route('admin.categories-conseil.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
        @endslot
    </x-page-header>

    <div class="card">
        <div class="card-body">
            @include('back.categories-conseil._form', [
                'categorie' => $categorie,
                'action' => route('admin.categories-conseil.store'),
                'method' => 'POST',
            ])
        </div>
    </div>
@endsection
