@extends('back.layouts.back')

@section('title', 'Nouveau conseil')

@section('content')
    <x-page-header title="Nouveau conseil" subtitle="Créer un conseil pratique">
        @slot('actions')
            <a href="{{ route('admin.conseils.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
        @endslot
    </x-page-header>

    <div class="card">
        <div class="card-body">
            @include('back.conseils._form', [
                'conseil' => $conseil,
                'categories' => $categories,
                'action' => route('admin.conseils.store'),
                'method' => 'POST',
            ])
        </div>
    </div>
@endsection
