@extends('back.layouts.back')

@section('title', 'Modifier une réservation')

@section('content')
    <x-page-header :title="'Modifier : ' . $reservation->code" subtitle="Modifier les informations de la réservation">
        @slot('actions')
            <a href="{{ route('admin.reservations.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
        @endslot
    </x-page-header>

    <div class="card">
        <div class="card-body">
            @include('back.reservations._form', [
                'reservation' => $reservation,
                'points' => $points,
                'action' => route('admin.reservations.update', $reservation),
                'method' => 'PUT',
            ])
        </div>
    </div>
@endsection
