@extends('front.layouts.front')
@section('content')
<section class="ve-section"><div class="container">
    <h1>Plans d'urgence</h1><p>Des gestes concrets pour préparer votre foyer.</p>
    <form method="GET" action="{{ route('plans-urgence.index') }}" class="form-row mb-4" novalidate>
        <div class="col-md-5"><select class="form-control" name="type"><option value="">Tous les événements</option>@foreach(\App\Models\PlanUrgence::TYPES as $k=>$v)<option value="{{ $k }}" @selected(request('type')===$k)>{{ $v }}</option>@endforeach</select></div>
        <div class="col-md-5"><select class="form-control" name="public"><option value="">Tous les publics</option>@foreach(\App\Models\PlanUrgence::PUBLICS as $k=>$v)<option value="{{ $k }}" @selected(request('public')===$k)>{{ $v }}</option>@endforeach</select></div>
        <div class="col-md-2"><button class="btn btn-primary">Filtrer</button></div>
    </form>
    @auth<button type="button" id="plans-personnalises" class="btn btn-outline-primary mb-3">Mon plan personnalisé avec l’IA</button><div id="plans-personnalises-resultat" aria-live="polite"></div>@endauth
    @if($recommandes->isNotEmpty())<h2>Plans recommandés pour vous</h2><div class="row">@foreach($recommandes->take(3) as $plan)<div class="col-md-4"><a href="{{ route('plans-urgence.show',$plan) }}">{{ $plan->titre }}</a></div>@endforeach</div>@endif
    <div class="row mt-4">@forelse($plans as $plan)<div class="col-md-4 mb-3"><article class="card h-100"><div class="card-body"><h3><a href="{{ route('plans-urgence.show',$plan) }}">{{ $plan->titre }}</a></h3><p>{{ $plan->description }}</p><span>{{ $plan->etapes_count }} étapes · {{ \App\Models\PlanUrgence::NIVEAUX[$plan->niveau_risque] }}</span></div></article></div>@empty<p>Aucun plan disponible.</p>@endforelse</div>
    <x-pagination :paginator="$plans" variant="bootstrap-4" />
</div></section>
@endsection
@auth
@push('scripts')<script>
document.getElementById('plans-personnalises').addEventListener('click',async function(){const out=document.getElementById('plans-personnalises-resultat');this.disabled=true;out.textContent='Recherche des plans adaptés…';try{const response=await fetch('{{ route('plans-urgence.ia.personnalise') }}',{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}});const data=await response.json();if(!response.ok)throw new Error(data.message||'Le service IA est momentanément indisponible.');const items=data.classement||data.plans||[];const list=document.createElement('ul');items.forEach(item=>{const li=document.createElement('li');const link=document.createElement('a');link.href=item.url;link.textContent=item.titre;li.append(link,document.createTextNode(` — ${item.justification}`));list.append(li);});out.replaceChildren(list);if(!items.length)out.textContent='Aucun plan actif disponible.';}catch(e){out.textContent=e.message;}finally{this.disabled=false;}});
</script>@endpush
@endauth
