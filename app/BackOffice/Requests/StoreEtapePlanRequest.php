<?php
namespace App\BackOffice\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreEtapePlanRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    protected function prepareForValidation(): void { $this->merge(['obligatoire' => $this->boolean('obligatoire')]); }
    public function rules(): array { return ['titre'=>['required','string','max:150'],'description'=>['nullable','string','max:500'],'moment'=>['required',Rule::in(['avant','pendant','apres'])],'ordre'=>['nullable','integer','min:1'],'obligatoire'=>['boolean']]; }
    public function messages(): array { return ['titre.required'=>'Le titre de l’étape est obligatoire.','titre.max'=>'Le titre ne peut dépasser 150 caractères.','description.max'=>'La description ne peut dépasser 500 caractères.','moment.required'=>'Le moment de l’étape est obligatoire.','moment.in'=>'Le moment choisi est invalide.','ordre.integer'=>'L’ordre doit être un nombre entier.','ordre.min'=>'L’ordre doit être supérieur à zéro.']; }
}
