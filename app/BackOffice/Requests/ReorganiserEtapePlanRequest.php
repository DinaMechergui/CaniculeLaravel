<?php
namespace App\BackOffice\Requests;
use Illuminate\Foundation\Http\FormRequest;
class ReorganiserEtapePlanRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['direction'=>['required','integer','in:-1,1']]; }
    public function messages(): array { return ['direction.required'=>'Le sens du déplacement est obligatoire.','direction.in'=>'Le déplacement demandé est invalide.']; }
}
