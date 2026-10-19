<?php
namespace App\BackOffice\Requests;
use App\Models\PlanUrgence;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class PlanAiRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['action'=>['required',Rule::in(['generer','etapes_manquantes','couverture'])],'type_evenement'=>['required_if:action,generer',Rule::in(array_keys(PlanUrgence::TYPES))],'niveau_risque'=>['required_if:action,generer',Rule::in(array_keys(PlanUrgence::NIVEAUX))],'public_cible'=>['required_if:action,generer',Rule::in(array_keys(PlanUrgence::PUBLICS))],'plan_id'=>['required_if:action,etapes_manquantes','nullable','integer','exists:plans_urgence,id']]; }
    public function messages(): array { return ['action.required'=>'Choisissez une action IA.','action.in'=>'L’action IA demandée est invalide.','type_evenement.required_if'=>'Choisissez un type d’événement.','type_evenement.in'=>'Le type d’événement est invalide.','niveau_risque.required_if'=>'Choisissez un niveau de risque.','niveau_risque.in'=>'Le niveau de risque est invalide.','public_cible.required_if'=>'Choisissez un public cible.','public_cible.in'=>'Le public cible est invalide.','plan_id.required_if'=>'Le plan est obligatoire pour proposer des étapes.','plan_id.exists'=>'Le plan sélectionné est introuvable.']; }
}
