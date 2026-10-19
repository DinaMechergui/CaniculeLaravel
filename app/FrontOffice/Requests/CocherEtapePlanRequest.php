<?php
namespace App\FrontOffice\Requests;
use Illuminate\Foundation\Http\FormRequest;
class CocherEtapePlanRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array { return ['etape_plan_id'=>['required','integer','exists:etapes_plan,id'],'fait'=>['required','boolean']]; }
    public function messages(): array { return ['etape_plan_id.required'=>'L’étape est obligatoire.','etape_plan_id.exists'=>'Cette étape n’existe pas.','fait.boolean'=>'L’état de l’étape est invalide.']; }
}
