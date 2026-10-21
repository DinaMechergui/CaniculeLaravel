@extends('back.layouts.back')
@section('title', 'Modifier l’intervention')
@section('content')
    <x-page-header :title="'Intervention #' . $intervention->id" subtitle="Modifier les informations de l’intervention">
        @slot('actions')<a href="{{ route('admin.interventions.show', $intervention) }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i> Retour au détail</a>@endslot
    </x-page-header>
    <div class="card"><div class="card-body">@include('back.interventions._form', ['action' => route('admin.interventions.update', $intervention), 'method' => 'PUT'])</div></div>
@endsection
