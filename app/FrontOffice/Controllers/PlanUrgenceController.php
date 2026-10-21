<?php
namespace App\FrontOffice\Controllers;
use App\FrontOffice\Requests\ProfilFoyerRequest;
use App\FrontOffice\Requests\CocherEtapePlanRequest;
use App\Http\Controllers\Controller;
use App\Models\EtapePlan;
use App\Models\PlanUrgence;
use App\Models\ProfilFoyer;
use App\Services\PlanUrgenceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
class PlanUrgenceController extends Controller
{
    public function index(Request $request, PlanUrgenceService $service) { $plans=PlanUrgence::withCount('etapes')->where('actif',true)->when($request->filled('type'),fn($q)=>$q->where('type_evenement',$request->query('type')))->when($request->filled('public'),fn($q)=>$q->where('public_cible',$request->query('public')))->paginate(9)->withQueryString(); $recommandes=$service->recommandes($request->user(),$plans->getCollection()); return view('front.plans-urgence.index',compact('plans','recommandes')); }
    public function show(PlanUrgence $planUrgence) { abort_unless($planUrgence->actif,404); $planUrgence->load('etapes'); $faits=auth()->user()?->etapesFaites()->whereIn('etapes_plan.id',$planUrgence->etapes->pluck('id'))->pluck('etapes_plan.id')->all() ?? []; $progression=auth()->check() ? app(PlanUrgenceService::class)->progression($planUrgence,auth()->user()) : null; return view('front.plans-urgence.show',['plan'=>$planUrgence,'faits'=>$faits,'progression'=>$progression]); }
    public function cocher(CocherEtapePlanRequest $request) { $data=$request->validated(); $etape=EtapePlan::with('planUrgence')->findOrFail($data['etape_plan_id']); abort_unless($etape->planUrgence->actif,404); $user=$request->user(); if($data['fait']) $user->etapesFaites()->syncWithoutDetaching([$etape->id=>['fait_le'=>now()]]); else $user->etapesFaites()->detach($etape->id); return $request->expectsJson()?response()->json(['ok'=>true]):back()->with('success','Progression mise à jour.'); }
    public function mesPlans(Request $request, PlanUrgenceService $service)
    {
        $user = $request->user();
        $ids = $user->etapesFaites()->get()->pluck('plan_urgence_id')->unique();
        $plans = PlanUrgence::with('etapes')->whereIn('id', $ids)->where('actif', true)->get();
        $plans->each(fn (PlanUrgence $plan) => $plan->setAttribute('progression', $service->progression($plan, $user)));
        $recommandes = $service->recommandes($user, PlanUrgence::withCount('etapes')->where('actif', true)->get())->take(3);
        $notifications = $user->unreadNotifications()->latest()->take(5)->get();
        return view('front.plans-urgence.mes-plans', compact('plans', 'recommandes', 'notifications'));
    }
    public function foyerEdit(Request $request) { $profil=$request->user()->profilFoyer ?? new ProfilFoyer(['nb_adultes'=>1]); return view('front.plans-urgence.foyer',['profil'=>$profil]); }
    public function foyerUpdate(ProfilFoyerRequest $request) { $request->user()->profilFoyer()->updateOrCreate([], $request->validated()); return back()->with('success','Votre profil foyer a été enregistré.'); }
    public function reinitialiser(Request $request, PlanUrgence $planUrgence) { $request->user()->etapesFaites()->detach($planUrgence->etapes()->pluck('id')); return back()->with('success','La progression de ce plan a été réinitialisée.'); }
    public function pdf(PlanUrgence $planUrgence)
    {
        abort_unless($planUrgence->actif, 404);
        $planUrgence->load('etapes');

        $qr = QrCode::format('svg')
            ->size(120)
            ->generate(route('plans-urgence.show', $planUrgence));

        return Pdf::loadView('front.plans-urgence.pdf', [
            'plan' => $planUrgence,
            'qr' => $qr,
        ])->download('plan-urgence-' . $planUrgence->id . '.pdf');
    }
}
