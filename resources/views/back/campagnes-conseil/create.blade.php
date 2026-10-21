@extends('back.layouts.back')

@section('title', 'Nouvelle campagne')

@section('content')
    <x-page-header title="Nouvelle campagne" subtitle="Créer une campagne de sensibilisation" />

    <div class="card">
        <div class="card-body">
            @include('back.campagnes-conseil._form', [
                'action' => route('admin.campagnes-conseil.store'),
                'method' => 'POST',
            ])
        </div>
    </div>
@endsection