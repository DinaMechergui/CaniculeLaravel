@extends('back.layouts.back')
@section('title', 'Programmer une diffusion')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <x-page-header title="Nouvelle diffusion de bulletin" subtitle="Envoyer ou programmer la transmission d'un bulletin d'alerte">
        @slot('actions')
            <a href="{{ route('admin.diffusions.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
        @endslot
    </x-page-header>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">Paramètres de diffusion</h5>
            <span class="badge bg-label-primary">Module complémentaire Diffusions</span>
        </div>
        <div class="card-body pt-4">
            <form action="{{ route('admin.diffusions.store') }}" method="POST">
                @csrf
                @include('back.diffusions._form')
            </form>
        </div>
    </div>
</div>
@endsection
