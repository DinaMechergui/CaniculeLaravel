@extends('back.layouts.back')

@section('title', 'Modifier le diagnostic #' . $diagnostic->id)

@section('content')
    <x-page-header
        title="Modifier le diagnostic"
        :subtitle="'#' . $diagnostic->id . ' — signalement #' . $diagnostic->signalement_id"
    >
        @slot('actions')
            <a href="{{ route('admin.diagnostics.show', $diagnostic) }}" class="btn btn-outline-secondary">
                <i class="bx bx-show me-1"></i> Voir
            </a>
            <a href="{{ route('admin.diagnostics.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
        @endslot
    </x-page-header>

    <div class="card">
        <div class="card-body">
            @include('back.diagnostics._form', [
                'action' => route('admin.diagnostics.update', $diagnostic),
                'method' => 'PUT',
            ])
        </div>
    </div>
@endsection
