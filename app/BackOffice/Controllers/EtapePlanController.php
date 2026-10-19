<?php
namespace App\BackOffice\Controllers;
use App\BackOffice\Requests\StoreEtapePlanRequest;
use App\Http\Controllers\Controller;
use App\Models\EtapePlan;
use App\Models\PlanUrgence;
class EtapePlanController extends Controller
{
    public function store(StoreEtapePlanRequest $request, PlanUrgence $planUrgence) { $d=$request->validated(); $d['ordre']=$d['ordre'] ?? ((int)$planUrgence->etapes()->max('ordre')+1); $planUrgence->etapes()->create($d); return back()->with('success','Étape ajoutée.'); }
    public function edit(EtapePlan $etapePlan) { return view('back.plans-urgence.etape-form',['etape'=>$etapePlan]); }
    public function update(StoreEtapePlanRequest $request, EtapePlan $etapePlan) { $etapePlan->update($request->validated()); return redirect()->route('admin.plans-urgence.show',$etapePlan->planUrgence)->with('success','Étape modifiée.'); }
    public function destroy(EtapePlan $etapePlan) { $plan=$etapePlan->planUrgence; $etapePlan->delete(); $plan->etapes()->orderBy('ordre')->get()->each(fn($e,$i)=>$e->update(['ordre'=>$i+1])); return back()->with('success','Étape supprimée.'); }
}
