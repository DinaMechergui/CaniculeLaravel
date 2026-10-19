<?php
namespace App\FrontOffice\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class ProfilFoyerRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    protected function prepareForValidation(): void { $this->merge(['a_animaux' => $this->boolean('a_animaux')]); }
    public function rules(): array { return ['nb_adultes'=>['required','integer','min:1','max:20'],'nb_enfants'=>['required','integer','min:0','max:20'],'nb_personnes_agees'=>['required','integer','min:0','max:20'],'a_animaux'=>['boolean'],'type_logement'=>['required',Rule::in(['appartement','maison'])]]; }
    public function messages(): array { return ['nb_adultes.required'=>'Indiquez le nombre d’adultes.','nb_adultes.integer'=>'Le nombre d’adultes doit être un entier.','nb_adultes.min'=>'Le foyer doit compter au moins un adulte.','nb_adultes.max'=>'Le maximum est de 20 adultes.','nb_enfants.required'=>'Indiquez le nombre d’enfants.','nb_enfants.integer'=>'Le nombre d’enfants doit être un entier.','nb_enfants.min'=>'Le nombre d’enfants ne peut être négatif.','nb_enfants.max'=>'Le maximum est de 20 enfants.','nb_personnes_agees.required'=>'Indiquez le nombre de personnes âgées.','nb_personnes_agees.integer'=>'Le nombre de personnes âgées doit être un entier.','nb_personnes_agees.min'=>'Le nombre de personnes âgées ne peut être négatif.','nb_personnes_agees.max'=>'Le maximum est de 20 personnes âgées.','type_logement.required'=>'Le type de logement est obligatoire.','type_logement.in'=>'Le type de logement est invalide.']; }
}
