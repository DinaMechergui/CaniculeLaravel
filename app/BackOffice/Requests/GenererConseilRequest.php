<?php
namespace App\BackOffice\Requests;
use Illuminate\Foundation\Http\FormRequest;
class GenererConseilRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['titre'=>['required','string','max:150'],'categorie'=>['nullable','string','max:100']]; }
    public function messages(): array { return ['titre.required'=>'Le titre du conseil est obligatoire.','titre.string'=>'Le titre doit être un texte.','titre.max'=>'Le titre ne peut dépasser 150 caractères.','categorie.string'=>'La catégorie doit être un texte.','categorie.max'=>'La catégorie ne peut dépasser 100 caractères.']; }
}
