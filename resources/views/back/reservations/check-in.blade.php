@extends('back.layouts.back')

@section('title', 'Check-in des réservations')

@section('content')
    <x-page-header title="Check-in" subtitle="Valider l'arrivée des clients par code de réservation" />

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card mb-4">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.reservations.check-in.store') }}" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-md-8">
                            <label class="form-label" for="code">Code de réservation (8 caractères)</label>
                            <input
                                type="text"
                                class="form-control form-control-lg text-uppercase"
                                id="code"
                                name="code"
                                value="{{ old('code', $code ?? '') }}"
                                placeholder="Ex. A1B2C3D4"
                                maxlength="8"
                                required
                                autofocus
                            >
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="bx bx-log-in me-1"></i> Vérifier
                            </button>
                        </div>
                        @error('code')
                            <div class="col-12 text-danger small">{{ $message }}</div>
                        @enderror
                    </form>
                </div>
            </div>

            @isset($reservation)
                <div class="card">
                    <div class="card-header">
                        <strong>Réservation {{ $reservation->code }}</strong>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <span class="badge bg-{{ $reservation->statut_badge }}">
                                {{ $reservation->statut_label }}
                            </span>
                            @if ($reservation->check_in_le)
                                <span class="badge bg-info ms-1">Déjà check-iné le {{ $reservation->check_in_le->format('d/m/Y H:i') }}</span>
                            @endif
                        </div>
                        <table class="table table-sm">
                            <tr><th style="width:40%;">Nom</th><td>{{ $reservation->nom }}</td></tr>
                            <tr><th>E-mail</th><td>{{ $reservation->email }}</td></tr>
                            <tr><th>Point</th><td>{{ $reservation->pointFraicheur?->nom ?? '—' }}</td></tr>
                            <tr><th>Date</th><td>{{ $reservation->date_reservation?->format('d/m/Y') }}</td></tr>
                            <tr><th>Créneau</th><td>{{ $reservation->heure_debut_courte }} – {{ $reservation->heure_fin_courte }}</td></tr>
                            <tr><th>Personnes</th><td>{{ $reservation->nombre_personnes }}</td></tr>
                        </table>

                        @unless ($reservation->check_in_le)
                            <form method="POST"
                                  action="{{ route('admin.reservations.marquer-check-in', $reservation) }}">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="bx bx-check me-1"></i> Valider l'arrivée
                                </button>
                            </form>
                        @else
                            <a href="{{ route('admin.reservations.show', $reservation) }}" class="btn btn-outline-primary">
                                Voir la réservation
                            </a>
                        @endunless
                    </div>
                </div>
            @else
                @if (filled($code ?? null))
                    <div class="alert alert-warning">Aucune réservation ne correspond au code saisi.</div>
                @endif
            @endisset
        </div>
    </div>
@endsection
