@extends('back.layouts.back')
@section('title', 'Nouvelle intervention')
@section('content')
    <x-page-header title="Nouvelle intervention" subtitle="Planifier ou enregistrer une intervention technique">
        @slot('actions')<a href="{{ route('admin.interventions.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i> Retour</a>@endslot
    </x-page-header>
    <div class="card"><div class="card-body">@include('back.interventions._form', ['action' => route('admin.interventions.store'), 'method' => 'POST'])</div></div>
@endsection
