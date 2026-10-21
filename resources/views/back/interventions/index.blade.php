@extends('back.layouts.back')
@section('title', 'Interventions')
@section('content')
    <x-page-header title="Interventions" :subtitle="'Suivi des interventions techniques (' . $interventions->total() . ')'">
        @slot('actions')<a href="{{ route('admin.interventions.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i> Nouvelle intervention</a>@endslot
    </x-page-header>
    <div class="card mb-4"><div class="card-body">
        <form method="GET" action="{{ route('admin.interventions.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-4"><label class="form-label" for="search">Technicien ou équipe</label><input class="form-control" id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Rechercher..."></div>
            <div class="col-12 col-md-3"><label class="form-label" for="quartier_id">Quartier</label><select class="form-select" id="quartier_id" name="quartier_id"><option value="">Tous les quartiers</option>@foreach($quartiers as $quartier)<option value="{{ $quartier->id }}" @selected((string) ($filters['quartier_id'] ?? '') === (string) $quartier->id)>{{ $quartier->nom }}</option>@endforeach</select></div>
            <div class="col-12 col-md-3"><label class="form-label" for="statut">Statut</label><select class="form-select" id="statut" name="statut"><option value="">Tous</option>@foreach(['prevue' => 'Prévue', 'en_cours' => 'En cours', 'terminee' => 'Terminée'] as $value => $label)<option value="{{ $value }}" @selected(($filters['statut'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-12 col-md-2 d-flex gap-2"><button class="btn btn-primary" type="submit">Filtrer</button><a class="btn btn-outline-secondary" href="{{ route('admin.interventions.index') }}">Effacer</a></div>
        </form>
    </div></div>
    <div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead class="table-dark"><tr><th>Intervention</th><th>Coupure / quartier</th><th>Technicien</th><th>Date</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
            @forelse($interventions as $intervention)
                <tr>
                    <td>{{ $intervention->equipe }}</td>
                    <td><a href="{{ route('admin.coupures.show', $intervention->coupure) }}">#{{ $intervention->coupure_id }} · {{ $intervention->coupure?->quartier?->nom }}</a></td>
                    <td>{{ $intervention->technicien }}</td>
                    <td>{{ $intervention->date_intervention->format('d/m/Y H:i') }}</td>
                    <td><span class="badge {{ ['prevue' => 'bg-warning text-dark', 'en_cours' => 'bg-primary', 'terminee' => 'bg-success'][$intervention->statut] }}">{{ str_replace('_', ' ', ucfirst($intervention->statut)) }}</span></td>
                    <td class="text-end"><div class="d-inline-flex gap-1"><a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.interventions.show', $intervention) }}" aria-label="Voir"><i class="bx bx-show"></i></a><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.interventions.edit', $intervention) }}" aria-label="Modifier"><i class="bx bx-edit"></i></a><form method="POST" action="{{ route('admin.interventions.destroy', $intervention) }}" onsubmit="return confirm('Confirmer la suppression de cette intervention ?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" aria-label="Supprimer"><i class="bx bx-trash"></i></button></form></div></td>
                </tr>
            @empty<tr><td colspan="6" class="text-center text-muted py-4">Aucune intervention trouvée.</td></tr>@endforelse
        </tbody>
    </table></div><div class="card-body">{{ $interventions->links() }}</div></div>
@endsection
