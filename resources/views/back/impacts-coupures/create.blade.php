@extends('back.layouts.back')
@section('title', 'Nouvel impact')
@section('content')
    <x-page-header title="Nouvel impact de coupure" subtitle="Déclarer une structure ou une population affectée">
        @slot('actions')<a href="{{ route('admin.impacts-coupures.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i> Retour</a>@endslot
    </x-page-header>
    <div class="card"><div class="card-body">@include('back.impacts-coupures._form', ['action' => route('admin.impacts-coupures.store'), 'method' => 'POST'])</div></div>
@endsection
