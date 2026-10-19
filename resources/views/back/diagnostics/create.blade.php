@extends('back.layouts.back')

@section('title', 'Nouveau diagnostic')

@section('content')
    <x-page-header title="Nouveau diagnostic" subtitle="Décrire les symptômes d'une panne signalée">
        @slot('actions')
            <a href="{{ route('admin.diagnostics.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
        @endslot
    </x-page-header>

    <div class="card">
        <div class="card-body">
            @include('back.diagnostics._form', [
                'action' => route('admin.diagnostics.store'),
                'method' => 'POST',
            ])
        </div>
    </div>
@endsection
