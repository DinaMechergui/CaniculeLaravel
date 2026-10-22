@extends('back.layouts.back')

@section('title', 'Détail d\'une réservation')

@section('content')
    <x-page-header :title="'Réservation ' . $reservation->code" :subtitle="'Réservation #' . $reservation->id">
        @slot('actions')
            <a href="{{ route('admin.reservations.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
            <a href="{{ route('admin.reservations.edit', $reservation) }}" class="btn btn-primary">
                <i class="bx bx-edit me-1"></i> Modifier
            </a>
        @endslot
    </x-page-header>

    <div class="row">
        {{-- Informations de la réservation --}}
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Créneau réservé</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Code</div>
                        <div class="col-sm-8"><code>{{ $reservation->code }}</code></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Statut</div>
                        <div class="col-sm-8">
                            <span class="badge bg-{{ $reservation->statut_badge }}">
                                {{ $reservation->statut_label }}
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Priorité</div>
                        <div class="col-sm-8">
                            <span class="badge bg-{{ $reservation->priorite_badge }}">
                                {{ $reservation->priorite_label }}
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Date</div>
                        <div class="col-sm-8">{{ $reservation->date_reservation?->format('d/m/Y') }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Horaires</div>
                        <div class="col-sm-8">
                            {{ $reservation->heure_debut_courte }} – {{ $reservation->heure_fin_courte }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4 text-muted">Personnes</div>
                        <div class="col-sm-8">{{ $reservation->nombre_personnes }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Client --}}
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Client</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Nom</div>
                        <div class="col-sm-8">{{ $reservation->nom }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">E-mail</div>
                        <div class="col-sm-8">
                            <a href="mailto:{{ $reservation->email }}">{{ $reservation->email }}</a>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Téléphone</div>
                        <div class="col-sm-8">{{ $reservation->telephone ?? '—' }}</div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4 text-muted">Créée le</div>
                        <div class="col-sm-8">{{ $reservation->created_at?->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Point de fraîcheur --}}
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Point de fraîcheur</h5>
                </div>
                <div class="card-body">
                    @if ($reservation->pointFraicheur)
                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted">Nom</div>
                            <div class="col-sm-8">
                                <a href="{{ route('admin.points-fraicheur.show', $reservation->pointFraicheur) }}">
                                    {{ $reservation->pointFraicheur->nom }}
                                </a>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted">Type</div>
                            <div class="col-sm-8">{{ $reservation->pointFraicheur->type?->nom ?? '—' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted">Adresse</div>
                            <div class="col-sm-8">{{ $reservation->pointFraicheur->adresse }}</div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 text-muted">Capacité</div>
                            <div class="col-sm-8">{{ $reservation->pointFraicheur->capacite }} personnes</div>
                        </div>
                    @else
                        <p class="text-muted mb-0">Point de fraîcheur introuvable.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Besoins et notes --}}
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Besoins spécifiques</h5>
                </div>
                <div class="card-body">
                    <p>{{ $reservation->besoins_specifiques ?: 'Aucun besoin particulier renseigné.' }}</p>

                    <hr>

                    <h6 class="text-muted">Notes internes</h6>
                    <p class="mb-0">{{ $reservation->notes_admin ?: 'Aucune note interne.' }}</p>
                </div>
            </div>
        </div>

        {{-- Analyse IA --}}
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="bx bx-brain me-1"></i> Analyse IA</h5>
                    <form action="{{ route('admin.reservations.analyser-ia', $reservation) }}" method="POST" class="d-inline mb-0" novalidate>
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-primary" title="Recalculer les étiquettes et le score de risque">
                            <i class="bx bx-refresh me-1"></i> Réanalyser avec l'IA
                        </button>
                    </form>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Étiquettes</div>
                        <div class="col-sm-8">
                            @forelse ((array) $reservation->tags_ia as $tag)
                                <span class="badge bg-info text-dark me-1">{{ str_replace('_', ' ', $tag) }}</span>
                            @empty
                                <span class="text-muted">—</span>
                            @endforelse
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Risque de no-show</div>
                        <div class="col-sm-8">
                            @if ($reservation->score_risque_no_show !== null)
                                @php $score = (float) $reservation->score_risque_no_show; @endphp
                                <div class="progress" style="height: 18px;">
                                    <div class="progress-bar bg-{{ $score >= 60 ? 'danger' : ($score >= 35 ? 'warning' : 'success') }}"
                                         role="progressbar" style="width: {{ $score }}%;"
                                         aria-valuenow="{{ $score }}" aria-valuemin="0" aria-valuemax="100">
                                        {{ $score }}%
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">Non calculé</span>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4 text-muted">Explication</div>
                        <div class="col-sm-8">{{ $reservation->explication_ia ?: '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Réponse assistée --}}
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="bx bx-edit me-1"></i> Réponse assistée par l'IA</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-primary me-2" id="btn-reponse-ia">
                            <i class="bx bx-magic-wand me-1"></i> Générer une réponse
                        </button>
                        <button type="button" class="btn btn-sm btn-primary" id="btn-envoyer-reponse">
                            <i class="bx bx-send me-1"></i> Envoyer par e-mail
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-2">
                        Le bouton propose une trame générée que vous pouvez modifier,
                        puis envoyer telle quelle à {{ $reservation->email }}.
                    </p>
                    <textarea id="reponse-ia" class="form-control" rows="7"
                              placeholder="Bonjour {{ $reservation->nom }}, ..."></textarea>
                    <div class="mt-2" id="info-envoi-reponse"></div>
                </div>
            </div>
        </div>

        {{-- Suivi --}}
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Suivi</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="text-muted">Confirmée le</div>
                            <div>{{ $reservation->confirmee_le?->format('d/m/Y H:i') ?? '—' }}</div>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="text-muted">Annulée le</div>
                            <div>{{ $reservation->annulee_le?->format('d/m/Y H:i') ?? '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted">Check-in le</div>
                            <div>{{ $reservation->check_in_le?->format('d/m/Y H:i') ?? '—' }}</div>
                        </div>
                    </div>

                    <hr>

                    <form method="POST"
                          action="{{ route('admin.reservations.destroy', $reservation) }}"
                          onsubmit="return confirm('Confirmer la suppression de cette réservation ?');" novalidate>
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="bx bx-trash me-1"></i> Supprimer cette réservation
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const bouton = document.getElementById('btn-reponse-ia');
            const zone = document.getElementById('reponse-ia');

            if (! bouton || ! zone) {
                return;
            }

            bouton.addEventListener('click', function () {
                bouton.disabled = true;
                const original = bouton.innerHTML;
                bouton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Génération…';

                fetch('{{ route('admin.reservations.reponse-ia', $reservation) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                })
                    .then((reponse) => reponse.json())
                    .then((donnees) => {
                        zone.value = donnees.reponse || '';
                    })
                    .catch(() => {
                        zone.value = 'Impossible de générer la réponse pour le moment.';
                    })
                    .finally(() => {
                        bouton.disabled = false;
                        bouton.innerHTML = original;
                    });
            });

            const boutonEnvoi = document.getElementById('btn-envoyer-reponse');
            const infoEnvoi = document.getElementById('info-envoi-reponse');

            if (boutonEnvoi && zone && infoEnvoi) {
                boutonEnvoi.addEventListener('click', function () {
                    const texte = zone.value.trim();

                    if (! texte) {
                        infoEnvoi.innerHTML = '<span class="text-danger small">Rédigez d\'abord une réponse.</span>';
                        return;
                    }

                    boutonEnvoi.disabled = true;
                    infoEnvoi.innerHTML = '<span class="text-muted small">Envoi en cours…</span>';

                    fetch('{{ route('admin.reservations.envoyer-reponse', $reservation) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ reponse: texte }),
                    })
                        .then((reponse) => reponse.json().then((donnees) => ({ ok: reponse.ok, donnees })))
                        .then(({ ok, donnees }) => {
                            infoEnvoi.innerHTML = ok
                                ? '<span class="text-success small">' + (donnees.message || 'Réponse envoyée.') + '</span>'
                                : '<span class="text-danger small">' + (donnees.message || 'Envoi impossible.') + '</span>';
                        })
                        .catch(() => {
                            infoEnvoi.innerHTML = '<span class="text-danger small">Erreur réseau : envoi impossible.</span>';
                        })
                        .finally(() => {
                            boutonEnvoi.disabled = false;
                        });
                });
            }
        });
    </script>
@endpush
