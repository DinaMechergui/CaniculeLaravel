@extends('back.layouts.back')

@section('title', 'Diagnostics')

@section('content')
    <x-page-header title="Diagnostics" :subtitle="'Diagnostics des pannes signalées (' . $diagnostics->total() . ')'">
        @slot('actions')
            <a href="{{ route('admin.diagnostics.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Nouveau diagnostic
            </a>
        @endslot
    </x-page-header>

    {{-- Filtres --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.diagnostics.index') }}" class="d-flex flex-wrap gap-2 align-items-end">
                <div>
                    <label class="form-label" for="zone">Zone touchée</label>
                    <select class="form-select" id="zone" name="zone">
                        <option value="">Toutes les zones</option>
                        @foreach (\App\Models\Diagnostic::ZONES as $key => $label)
                            <option value="{{ $key }}" @selected($zone === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bx bx-search me-1"></i> Filtrer
                </button>
                @if ($zone)
                    <a href="{{ route('admin.diagnostics.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                @endif
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Signalement</th>
                            <th>Zone</th>
                            <th>Heure</th>
                            <th>Temp.</th>
                            <th>Cause prédite (IA)</th>
                            <th>Danger (IA)</th>
                            <th>Cause réelle</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($diagnostics as $diagnostic)
                            <tr>
                                <td class="text-muted">{{ $diagnostic->id }}</td>
                                <td>
                                    @if ($diagnostic->signalement)
                                        <a href="{{ route('admin.signalements.show', $diagnostic->signalement) }}">
                                            #{{ $diagnostic->signalement->id }}
                                        </a>
                                        <small class="text-muted d-block">
                                            {{ $diagnostic->signalement->typeSignalement?->libelle ?? '—' }}
                                        </small>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ \App\Models\Diagnostic::ZONES[$diagnostic->zone_touchee] ?? $diagnostic->zone_touchee }}</td>
                                <td class="text-nowrap">{{ $diagnostic->heure_panne }}</td>
                                <td class="text-nowrap">{{ $diagnostic->temperature !== null ? $diagnostic->temperature . ' °C' : '—' }}</td>
                                <td>
                                    @if ($diagnostic->estAnalyse())
                                        {{ \App\Models\Diagnostic::CAUSES[$diagnostic->cause_predite] ?? $diagnostic->cause_predite }}
                                        <small class="text-muted">({{ round($diagnostic->confiance * 100) }} %)</small>
                                    @else
                                        <span class="badge bg-label-secondary">En attente</span>
                                    @endif
                                </td>
                                <td>
                                    @include('back.diagnostics._badge-danger', ['niveau' => $diagnostic->niveau_danger])
                                </td>
                                <td>
                                    {{ $diagnostic->cause_reelle ? \App\Models\Diagnostic::CAUSES[$diagnostic->cause_reelle] : '—' }}
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('admin.diagnostics.show', $diagnostic) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Voir">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        <a href="{{ route('admin.diagnostics.edit', $diagnostic) }}"
                                           class="btn btn-sm btn-outline-primary" title="Modifier">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        <form method="POST"
                                              action="{{ route('admin.diagnostics.destroy', $diagnostic) }}"
                                              onsubmit="return confirm('Supprimer définitivement ce diagnostic ?');">
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
                                    <i class="bx bx-wrench mb-2" style="font-size:2rem;display:block;"></i>
                                    Aucun diagnostic enregistré.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    <x-pagination :paginator="$diagnostics" variant="bootstrap-5" />
@endsection
