<?php
namespace App\FrontOffice\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class TransformationPlanRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['action'=>['required',Rule::in(['traduire','simplifier'])]]; }
    public function messages(): array { return ['action.required'=>'Choisissez une transformation.','action.in'=>'La transformation demandée est invalide.']; }
}
