@extends('back.layouts.back')

@section('title', 'Modifier la campagne')

@section('content')
    <x-page-header title="Modifier la campagne" :subtitle="$campagne->titre" />

    <div class="card">
        <div class="card-body">
            @include('back.campagnes-conseil._form', [
                'action' => route('admin.campagnes-conseil.update', $campagne),
                'method' => 'PUT',
            ])
        </div>
    </div>
@endsection