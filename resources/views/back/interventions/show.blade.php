@extends('back.layouts.back')
@section('title', 'Détail de l’intervention')
@section('content')
    <x-page-header :title="'Intervention #' . $intervention->id" subtitle="Détail de l’intervention technique">
        @slot('actions')
            <a href="{{ route('admin.interventions.index') }}" class="btn btn-outline-secondary">Retour</a>
            <a href="{{ route('admin.interventions.edit', $intervention) }}" class="btn btn-primary">Modifier</a>
        @endslot
    </x-page-header>
    <div class="card"><div class="card-body">
        <div class="row g-3">
            <div class="col-md-6"><span class="text-muted">Coupure</span><p><a href="{{ route('admin.coupures.show', $intervention->coupure) }}">#{{ $intervention->coupure_id }} — {{ $intervention->coupure?->quartier?->nom }}</a></p></div>
            <div class="col-md-6"><span class="text-muted">Équipe</span><p>{{ $intervention->equipe }}</p></div>
            <div class="col-md-6"><span class="text-muted">Technicien</span><p>{{ $intervention->technicien }}</p></div>
            <div class="col-md-6"><span class="text-muted">Date</span><p>{{ $intervention->date_intervention->format('d/m/Y H:i') }}</p></div>
            <div class="col-md-6"><span class="text-muted">Durée estimée</span><p>{{ $intervention->duree_estimee }} minutes</p></div>
            <div class="col-md-6"><span class="text-muted">Statut</span><p><span class="badge {{ ['prevue' => 'bg-warning text-dark', 'en_cours' => 'bg-primary', 'terminee' => 'bg-success'][$intervention->statut] }}">{{ str_replace('_', ' ', ucfirst($intervention->statut)) }}</span></p></div>
            <div class="col-12"><span class="text-muted">Rapport technique</span><p class="mb-0">{{ $intervention->rapport ?: 'Aucun rapport renseigné.' }}</p></div>
        </div>
    </div></div>
@endsection
