@extends('front.layouts.front')

@section('title', 'Campagnes de sensibilisation')
@section('description', 'Campagnes de sensibilisation canicule et coupures de courant.')

@section('content')
    <section class="ve-section">
        <div class="container">
            <div class="ve-section-header text-center">
                <span class="ve-section-tag">Campagnes</span>
                <h1>Nos <span>campagnes</span> de sensibilisation</h1>
                <p>
                    Des actions de communication pour informer et protéger tous les habitants.
                    {{ $campagnes->total() }} campagne(s) active(s).
                </p>
            </div>

            {{-- Filtres --}}
            <form method="GET" action="{{ route('campagnes.index') }}" class="form-row justify-content-center mb-4" novalidate>
                <div class="col-12 col-md-4 mb-2">
                    <select name="public" class="form-control">
                        <option value="">Tous les publics</option>
                        @foreach ($publics as $valeur => $libelle)
                            <option value="{{ $valeur }}" @selected($publicFiltre === $valeur)>{{ $libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4 mb-2">
                    <select name="canal" class="form-control">
                        <option value="">Tous les canaux</option>
                        @foreach ($canaux as $valeur => $libelle)
                            <option value="{{ $valeur }}" @selected(($canalFiltre ?? '') === $valeur)>{{ $libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-auto mb-2">
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                    @if ($publicFiltre || ($canalFiltre ?? ''))
                        <a href="{{ route('campagnes.index') }}" class="btn btn-link">Réinitialiser</a>
                    @endif
                </div>
            </form>

            <div class="row">
                @forelse ($campagnes as $campagne)
                    <div class="col-12 col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body d-flex flex-column">
                                <div class="mb-2">
                                    <span class="badge badge-success">Active</span>
                                    <span class="badge badge-info ml-1">{{ $campagne->canal_label }}</span>
                                    @if ($campagne->created_at && $campagne->created_at->diffInDays(now()) <= 7)
                                        <span class="badge badge-primary ml-1">Nouvelle</span>
                                    @endif
                                    @if ($campagne->jours_restants !== null && $campagne->jours_restants <= 7)
                                        <span class="badge badge-danger ml-1">Se termine dans {{ $campagne->jours_restants }} j</span>
                                    @endif
                                </div>
                                <h5 class="card-title">{{ $campagne->titre }}</h5>
                                <p class="card-text text-muted small">
                                    <i class="fa fa-users" aria-hidden="true"></i> {{ $campagne->public_label }}<br>
                                    <i class="fa fa-calendar" aria-hidden="true"></i>
                                    {{ $campagne->date_debut->format('d/m/Y') }}
                                    →
                                    {{ $campagne->date_fin ? $campagne->date_fin->format('d/m/Y') : 'Sans fin' }}
                                </p>
                                @if ($campagne->message)
                                    <p class="card-text small"><i class="fa fa-bullhorn" aria-hidden="true"></i> {{ \Illuminate\Support\Str::limit($campagne->message, 100) }}</p>
                                @endif
                                <a href="{{ route('campagnes.show', $campagne) }}" class="mt-auto btn btn-outline-primary btn-sm">
                                    Voir la campagne <i class="fa fa-angle-right" aria-hidden="true"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-5">
                        Aucune campagne active pour le moment.
                    </div>
                @endforelse
            </div>

            <x-pagination :paginator="$campagnes" variant="bootstrap-4" />
        </div>
    </section>
@endsection
