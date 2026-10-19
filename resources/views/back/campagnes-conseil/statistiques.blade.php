@extends('back.layouts.back')

@section('title', 'Statistiques des campagnes')

@section('content')
    <x-page-header title="Statistiques des campagnes" subtitle="Vue d'ensemble du portefeuille">
        @slot('actions')
            <a href="{{ route('admin.campagnes-conseil.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
        @endslot
    </x-page-header>

    {{-- Totaux par statut --}}
    <div class="row mb-4">
        @foreach (\App\Models\CampagneConseil::STATUTS as $clef => $libelle)
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="app-stat-card">
                            <div class="app-stat-icon">
                                <i class="bx bx-{{ $clef === 'active' ? 'check-circle' : ($clef === 'terminee' ? 'time-five' : 'edit') }}"></i>
                            </div>
                            <div>
                                <div class="app-stat-value">{{ $stats['parStatut'][$clef] ?? 0 }}</div>
                                <div class="app-stat-label">{{ $libelle }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row mb-4">
        {{-- Par canal --}}
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header fw-semibold">Par canal</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tbody>
                            @foreach (\App\Models\CampagneConseil::CANAUX as $clef => $libelle)
                                <tr>
                                    <td>{{ $libelle }}</td>
                                    <td class="text-end fw-semibold">{{ $stats['parCanal'][$clef] ?? 0 }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Par public --}}
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header fw-semibold">Par public cible</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tbody>
                            @foreach (\App\Models\CampagneConseil::PUBLICS as $clef => $libelle)
                                <tr>
                                    <td>{{ $libelle }}</td>
                                    <td class="text-end fw-semibold">{{ $stats['parPublic'][$clef] ?? 0 }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Top 5 conseils --}}
    <div class="card mb-4">
        <div class="card-header fw-semibold">Top 5 des conseils les plus diffusés</div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr><th>Conseil</th><th class="text-end">Campagnes</th></tr>
                </thead>
                <tbody>
                    @forelse ($stats['top5Conseils'] as $item)
                        <tr>
                            <td>{{ $item->conseil->titre ?? '—' }}</td>
                            <td class="text-end">{{ $item->nb_campagnes }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="text-center text-muted py-3">Aucune donnée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Conseils sans campagne --}}
    @if ($stats['conseilsSansCampagne']->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header fw-semibold text-warning">
                <i class="bx bx-error me-1"></i> Conseils sans campagne ({{ $stats['conseilsSansCampagne']->count() }})
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach ($stats['conseilsSansCampagne'] as $conseil)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="{{ route('admin.conseils.show', $conseil) }}">{{ $conseil->titre }}</a>
                            <span class="badge bg-light text-dark">{{ $conseil->categorie->nom ?? '—' }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Campagnes qui finissent dans 7 jours --}}
    @if ($stats['finDansSetJours']->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header fw-semibold text-danger">
                <i class="bx bx-calendar-exclamation me-1"></i> Campagnes actives se terminant dans 7 jours
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr><th>Titre</th><th>Conseil</th><th>Date de fin</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($stats['finDansSetJours'] as $c)
                            <tr>
                                <td><a href="{{ route('admin.campagnes-conseil.show', $c) }}">{{ $c->titre }}</a></td>
                                <td>{{ $c->conseil->titre ?? '—' }}</td>
                                <td>{{ $c->date_fin->format('d/m/Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Calendrier mensuel --}}
    <div class="card mb-4">
        <div class="card-header fw-semibold">Calendrier mensuel</div>
        <div class="card-body">
            @forelse ($calendrier as $mois => $campagnesDuMois)
                <h6 class="mt-3">{{ \Carbon\Carbon::createFromFormat('Y-m', $mois)->translatedFormat('F Y') }}</h6>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @foreach ($campagnesDuMois as $c)
                        <a href="{{ route('admin.campagnes-conseil.show', $c) }}"
                           class="badge bg-{{ $c->couleur_calendrier }} text-decoration-none"
                           title="{{ $c->etat_calendrier }}">
                            {{ $c->titre }}
                        </a>
                    @endforeach
                </div>
            @empty
                <p class="text-muted">Aucune campagne.</p>
            @endforelse
        </div>
    </div>

    <div class="card mb-4"><div class="card-header fw-semibold">Analyse IA du portefeuille</div><div class="card-body"><button type="button" id="ia-analyse-campagnes" class="btn btn-outline-primary">Analyser les statistiques</button><div id="ia-analyse-resultat" class="mt-3" aria-live="polite"></div></div></div>
    <div class="card mb-4"><div class="card-header fw-semibold">Proposer des campagnes pour les conseils non diffusés</div><div class="card-body"><button type="button" id="ia-proposer-campagnes" class="btn btn-outline-primary">Proposer jusqu’à cinq brouillons</button><div id="ia-propositions-feedback" class="mt-3" aria-live="polite"></div><form id="ia-propositions-form" method="POST" action="{{ route('admin.campagnes-conseil.ia.selection') }}" novalidate class="d-none">@csrf<div id="ia-propositions-table" class="table-responsive"></div><button class="btn btn-primary mt-3">Créer les brouillons sélectionnés</button></form></div></div>
@endsection

@push('scripts')
<script>
const campagneAiUrl='{{ route('admin.campagnes-conseil.ia') }}';
const campagneCsrf='{{ csrf_token() }}';
async function demandeCampagneIa(payload){
    const response=await fetch(campagneAiUrl,{method:'POST',headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':campagneCsrf},body:JSON.stringify(payload)});
    const body=await response.json(); if(!response.ok) throw new Error(body.message||Object.values(body.errors||{}).flat().join(' ')); return body;
}
document.getElementById('ia-analyse-campagnes').addEventListener('click',async function(){const out=document.getElementById('ia-analyse-resultat');this.disabled=true;out.textContent='Analyse en cours…';try{const r=await demandeCampagneIa({action:'analyse'});out.replaceChildren();for(const key of ['constats','lacunes','actions']){const section=document.createElement('section');const title=document.createElement('h6');title.textContent=key[0].toUpperCase()+key.slice(1);section.append(title);const list=document.createElement('ul');r[key].forEach(value=>{const li=document.createElement('li');li.textContent=value;list.append(li);});section.append(list);out.append(section);}}catch(e){out.textContent=e.message;}finally{this.disabled=false;}});
document.getElementById('ia-proposer-campagnes').addEventListener('click',async function(){const out=document.getElementById('ia-propositions-feedback');this.disabled=true;out.textContent='Propositions en cours…';try{const r=await demandeCampagneIa({action:'propositions'});if(!r.propositions.length){out.textContent='Aucun conseil sans campagne à proposer.';return;}const form=document.getElementById('ia-propositions-form');const table=document.createElement('table');table.className='table';const head=document.createElement('thead');head.innerHTML='<tr><th>Sélection</th><th>Titre</th><th>Message</th><th>Public</th><th>Canal</th><th>Début</th><th>Fin</th></tr>';table.append(head);const body=document.createElement('tbody');r.propositions.forEach((item,index)=>{const row=document.createElement('tr');const fields={conseil_id:item.conseil_id,titre:item.titre,message:item.message,public_cible:item.public_cible,canal:item.canal,date_debut:item.date_debut,date_fin:item.date_fin};const checkCell=document.createElement('td');const check=document.createElement('input');check.type='checkbox';check.checked=true;checkCell.append(check);row.append(checkCell);Object.entries(fields).forEach(([name,value])=>{const cell=document.createElement('td');const input=document.createElement('input');input.className='form-control';input.name=`campagnes[${index}][${name}]`;input.value=value??'';if(name==='conseil_id')input.type='hidden';cell.append(input);row.append(cell);});check.addEventListener('change',()=>row.querySelectorAll('input:not([type=checkbox])').forEach(input=>input.disabled=!check.checked));body.append(row);});table.append(body);document.getElementById('ia-propositions-table').replaceChildren(table);form.classList.remove('d-none');out.textContent='Relisez et modifiez les propositions, puis cochez celles à créer.';}catch(e){out.textContent=e.message;}finally{this.disabled=false;}});
</script>
@endpush
