@extends('back.layouts.back')

@section('title', 'Modifier un conseil')

@section('content')
    <x-page-header :title="'Modifier : ' . $conseil->titre" subtitle="Modifier un conseil pratique">
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
                'action' => route('admin.conseils.update', $conseil),
                'method' => 'PUT',
            ])
        </div>
    </div>
@endsection
