<?php
namespace App\BackOffice\Requests;
use App\Models\PlanUrgence;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StorePlanUrgenceRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    protected function prepareForValidation(): void { $this->merge(['actif' => $this->boolean('actif')]); }
    public function rules(): array { return ['titre' => ['required','string','max:150'],'description' => ['nullable','string','max:1000'],'type_evenement' => ['required',Rule::in(array_keys(PlanUrgence::TYPES))],'niveau_risque' => ['required',Rule::in(array_keys(PlanUrgence::NIVEAUX))],'public_cible' => ['required',Rule::in(array_keys(PlanUrgence::PUBLICS))],'actif' => ['boolean']]; }
    public function messages(): array { return ['titre.required' => 'Le titre est obligatoire.','titre.max' => 'Le titre ne peut dépasser 150 caractères.','description.max' => 'La description ne peut dépasser 1 000 caractères.','type_evenement.required' => 'Le type d’événement est obligatoire.','type_evenement.in' => 'Le type d’événement est invalide.','niveau_risque.required' => 'Le niveau de risque est obligatoire.','niveau_risque.in' => 'Le niveau de risque est invalide.','public_cible.required' => 'Le public cible est obligatoire.','public_cible.in' => 'Le public cible est invalide.']; }
}
