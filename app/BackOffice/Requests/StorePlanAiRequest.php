<?php
namespace App\BackOffice\Requests;
use App\Models\PlanUrgence;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StorePlanAiRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['titre'=>['required','string','max:150'],'description'=>['nullable','string','max:1000'],'type_evenement'=>['required',Rule::in(array_keys(PlanUrgence::TYPES))],'niveau_risque'=>['required',Rule::in(array_keys(PlanUrgence::NIVEAUX))],'public_cible'=>['required',Rule::in(array_keys(PlanUrgence::PUBLICS))],'etapes'=>['required','array','min:8','max:12'],'etapes.*.titre'=>['required','string','max:150'],'etapes.*.description'=>['nullable','string','max:500'],'etapes.*.moment'=>['required',Rule::in(['avant','pendant','apres'])],'etapes.*.obligatoire'=>['required','boolean']]; }
    public function messages(): array { return ['titre.required'=>'Le titre du plan est obligatoire.','titre.max'=>'Le titre ne peut dépasser 150 caractères.','type_evenement.in'=>'Le type d’événement est invalide.','niveau_risque.in'=>'Le niveau de risque est invalide.','public_cible.in'=>'Le public cible est invalide.','etapes.min'=>'Un plan IA doit contenir au moins huit étapes.','etapes.max'=>'Un plan IA ne peut dépasser douze étapes.','etapes.*.titre.required'=>'Chaque étape doit avoir un titre.','etapes.*.moment.in'=>'Le moment d’une étape est invalide.']; }
}
