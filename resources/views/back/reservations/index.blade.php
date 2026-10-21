@extends('back.layouts.back')

@section('title', 'Réservations')

@section('content')
    <x-page-header title="Réservations" :subtitle="'Liste des réservations (' . $reservations->total() . ')'">
        @slot('actions')
            <a href="{{ route('admin.reservations.calendrier') }}" class="btn btn-outline-info">
                <i class="bx bx-calendar me-1"></i> Calendrier
            </a>
            <a href="{{ route('admin.reservations.analytique') }}" class="btn btn-outline-dark">
                <i class="bx bx-brain me-1"></i> Analyse IA
            </a>
            <a href="{{ route('admin.reservations.check-in') }}" class="btn btn-outline-primary">
                <i class="bx bx-log-in me-1"></i> Check-in
            </a>
            <a href="{{ route('admin.reservations.export', request()->query()) }}" class="btn btn-outline-success">
                <i class="bx bx-download me-1"></i> Exporter CSV
            </a>
        @endslot
    </x-page-header>

    {{-- Filtres et recherche --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reservations.index') }}" class="row g-2 align-items-end" novalidate>
                <div class="col-md-3">
                    <label class="form-label" for="search">Rechercher</label>
                    <input
                        type="text"
                        class="form-control"
                        id="search"
                        name="search"
                        value="{{ $filtres['search'] }}"
                        placeholder="Code, nom ou e-mail..."
                    >
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="statut">Statut</label>
                    <select class="form-control" id="statut" name="statut">
                        <option value="">Tous les statuts</option>
                        @foreach (\App\Models\Reservation::STATUTS as $valeur => $libelle)
                            <option value="{{ $valeur }}" @selected($filtres['statut'] === $valeur)>{{ $libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="priorite">Priorité</label>
                    <select class="form-control" id="priorite" name="priorite">
                        <option value="">Toutes</option>
                        @foreach (\App\Models\Reservation::PRIORITES as $valeur => $libelle)
                            <option value="{{ $valeur }}" @selected($filtres['priorite'] === $valeur)>{{ $libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="point">Point de fraîcheur</label>
                    <select class="form-control" id="point" name="point">
                        <option value="">Tous les points</option>
                        @foreach ($points as $point)
                            <option value="{{ $point->id }}" @selected((string) $filtres['point'] === (string) $point->id)>
                                {{ $point->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="date">Date</label>
                    <input type="date" class="form-control" id="date" name="date" value="{{ $filtres['date'] }}">
                </div>
                <div class="col-12 d-flex gap-2 mt-2">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bx bx-filter-alt me-1"></i> Filtrer
                    </button>
                    @if (collect($filtres)->filter()->isNotEmpty())
                        <a href="{{ route('admin.reservations.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Liste des réservations --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Code</th>
                            <th>Client</th>
                            <th>Point</th>
                            <th>Date</th>
                            <th>Créneau</th>
                            <th>Pers.</th>
                            <th>Statut</th>
                            <th>Priorité</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reservations as $reservation)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.reservations.show', $reservation) }}" class="fw-semibold">
                                        {{ $reservation->code }}
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $reservation->nom }}</div>
                                    <small class="text-muted">{{ $reservation->email }}</small>
                                </td>
                                <td>{{ $reservation->pointFraicheur?->nom ?? '—' }}</td>
                                <td>{{ $reservation->date_reservation?->format('d/m/Y') }}</td>
                                <td>{{ $reservation->heure_debut_courte }} – {{ $reservation->heure_fin_courte }}</td>
                                <td>{{ $reservation->nombre_personnes }}</td>
                                <td>
                                    <span class="badge bg-{{ $reservation->statut_badge }}">
                                        {{ $reservation->statut_label }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $reservation->priorite_badge }}">
                                        {{ $reservation->priorite_label }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        @if ($reservation->statut === \App\Models\Reservation::STATUT_EN_ATTENTE)
                                            <form method="POST"
                                                  action="{{ route('admin.reservations.confirmer', $reservation) }}" novalidate>
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Confirmer">
                                                    <i class="bx bx-check"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if ($reservation->statut === \App\Models\Reservation::STATUT_CONFIRMEE)
                                            <form method="POST"
                                                  action="{{ route('admin.reservations.marquer-check-in', $reservation) }}" novalidate>
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-info" title="Check-in">
                                                    <i class="bx bx-log-in"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if (in_array($reservation->statut, [\App\Models\Reservation::STATUT_EN_ATTENTE, \App\Models\Reservation::STATUT_CONFIRMEE, \App\Models\Reservation::STATUT_LISTE_ATTENTE], true))
                                            <form method="POST"
                                                  action="{{ route('admin.reservations.annuler', $reservation) }}"
                                                  onsubmit="return confirm('Confirmer l\\'annulation de cette réservation ?');" novalidate>
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-warning" title="Annuler">
                                                    <i class="bx bx-x"></i>
                                                </button>
                                            </form>
                                        @endif

                                        <a href="{{ route('admin.reservations.show', $reservation) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Voir">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        <a href="{{ route('admin.reservations.edit', $reservation) }}"
                                           class="btn btn-sm btn-outline-primary" title="Modifier">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        <form method="POST"
                                              action="{{ route('admin.reservations.destroy', $reservation) }}"
                                              onsubmit="return confirm('Confirmer la suppression de cette réservation ?');" novalidate>
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    Aucune réservation ne correspond à votre recherche.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    <x-pagination :paginator="$reservations" variant="bootstrap-5" />
@endsection
