@extends('back.layouts.back')

@section('title', 'Modifier la mesure de prévention')

@section('content')
    <x-page-header title="Modifier la mesure de prévention" :subtitle="'Modification de : ' . $mesurePrevention->titre">
        @slot('actions')
            <a href="{{ route('admin.mesures-prevention.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
        @endslot
    </x-page-header>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.mesures-prevention.update', $mesurePrevention) }}">
                @method('PUT')
                @include('back.mesures-prevention._form', ['isEdit' => true])
            </form>
        </div>
    </div>
@endsection
