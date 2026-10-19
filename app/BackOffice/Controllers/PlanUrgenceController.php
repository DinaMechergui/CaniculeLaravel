<?php
namespace App\BackOffice\Controllers;
use App\BackOffice\Requests\StorePlanUrgenceRequest;
use App\BackOffice\Requests\ReorganiserEtapePlanRequest;
use App\Http\Controllers\Controller;
use App\Models\PlanUrgence;
use App\Services\PlanUrgenceService;
use Illuminate\Http\Request;
class PlanUrgenceController extends Controller
{
    public function index(Request $request) { $plans=PlanUrgence::withCount('etapes')->when($request->filled('type'),fn($q)=>$q->where('type_evenement',$request->query('type')))->when($request->filled('niveau'),fn($q)=>$q->where('niveau_risque',$request->query('niveau')))->when($request->filled('public'),fn($q)=>$q->where('public_cible',$request->query('public')))->when($request->filled('actif'),fn($q)=>$q->where('actif',$request->boolean('actif')))->when($request->filled('q'),fn($q)=>$q->where('titre','like','%'.$request->query('q').'%'))->latest()->paginate(10)->withQueryString(); return view('back.plans-urgence.index',compact('plans')); }
    public function create() { return view('back.plans-urgence.form',['plan'=>new PlanUrgence]); }
    public function store(StorePlanUrgenceRequest $request) { $data=$request->validated(); $data['actif']=false; $plan=PlanUrgence::create($data); return redirect()->route('admin.plans-urgence.show',$plan)->with('success','Le plan a été créé.'); }
    public function show(PlanUrgence $planUrgence) { $planUrgence->load('etapes'); return view('back.plans-urgence.show',['plan'=>$planUrgence]); }
    public function edit(PlanUrgence $planUrgence) { return view('back.plans-urgence.form',['plan'=>$planUrgence]); }
    public function update(StorePlanUrgenceRequest $request, PlanUrgence $planUrgence) { $data=$request->validated(); unset($data['actif']); $planUrgence->update($data); return redirect()->route('admin.plans-urgence.show',$planUrgence)->with('success','Le plan a été modifié.'); }
    public function destroy(PlanUrgence $planUrgence) { $n=$planUrgence->etapes()->count(); $planUrgence->delete(); return redirect()->route('admin.plans-urgence.index')->with('success',"Le plan et ses {$n} étapes ont été supprimés."); }
    public function activer(PlanUrgence $planUrgence, PlanUrgenceService $service) { return $service->activer($planUrgence) ? back()->with('success','Le plan est actif.') : back()->with('error','Ajoutez au moins trois étapes, dont une obligatoire, avant activation.'); }
    public function desactiver(PlanUrgence $planUrgence) { $planUrgence->update(['actif'=>false]); return back()->with('success','Le plan a été désactivé.'); }
    public function dupliquer(PlanUrgence $planUrgence, PlanUrgenceService $service) { $copie=$service->dupliquer($planUrgence); return redirect()->route('admin.plans-urgence.edit',$copie)->with('success','Le plan a été dupliqué.'); }
    public function statistiques(PlanUrgenceService $service) { $stats=$service->statistiques(); return view('back.plans-urgence.statistiques',compact('stats')); }
    public function reorganiser(ReorganiserEtapePlanRequest $request, PlanUrgence $planUrgence, \App\Models\EtapePlan $etapePlan, PlanUrgenceService $service) { abort_unless($etapePlan->plan_urgence_id===$planUrgence->id,404); $service->reorganiser($etapePlan,(int)$request->validated('direction')); return back()->with('success','Ordre des étapes mis à jour.'); }
}
