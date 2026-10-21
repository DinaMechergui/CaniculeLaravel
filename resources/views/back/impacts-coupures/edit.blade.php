@extends('back.layouts.back')
@section('title', 'Modifier un impact')
@section('content')
    <x-page-header :title="'Impact #' . $impact->id" subtitle="Modifier les informations de l’impact">
        @slot('actions')<a href="{{ route('admin.impacts-coupures.show', $impact) }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i> Retour au détail</a>@endslot
    </x-page-header>
    <div class="card"><div class="card-body">@include('back.impacts-coupures._form', ['action' => route('admin.impacts-coupures.update', $impact), 'method' => 'PUT'])</div></div>
@endsection
