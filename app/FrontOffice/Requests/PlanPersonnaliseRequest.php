<?php
namespace App\FrontOffice\Requests;
use Illuminate\Foundation\Http\FormRequest;
class PlanPersonnaliseRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array { return []; }
    public function messages(): array { return []; }
}
