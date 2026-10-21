<?php

namespace App\BackOffice\Requests;

use App\BackOffice\Requests\Concerns\HasCoordinatesValidation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCoupureRequest extends FormRequest
{
    use HasCoordinatesValidation;

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return array_merge([
            'quartier_id' => ['required', 'exists:quartiers,id'],
            'type' => ['required', 'in:delestage,panne,surcharge'],
            'statut' => ['required', 'in:prevue,en_cours,terminee'],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['nullable', 'date', 'after:date_debut'],
            'description' => ['nullable', 'string'],
        ], $this->coordinateRules());
    }

    public function messages(): array
    {
        return array_merge([
            'quartier_id.required' => 'Veuillez sélectionner un quartier.',
            'quartier_id.exists' => 'Le quartier sélectionné n’existe pas.',
            'type.required' => 'Le type de coupure est obligatoire.',
            'type.in' => 'Le type de coupure sélectionné est invalide.',
            'statut.required' => 'Le statut de la coupure est obligatoire.',
            'statut.in' => 'Le statut sélectionné est invalide.',
            'date_debut.required' => 'La date de début est obligatoire.',
            'date_debut.date' => 'La date de début est invalide.',
            'date_fin.date' => 'La date de fin est invalide.',
            'date_fin.after' => 'La date de fin doit être postérieure à la date de début.',
            'description.string' => 'La description doit être une chaîne de caractères.',
        ], $this->coordinateMessages());
    }

    protected function prepareForValidation(): void
    {
        $this->prepareCoordinatesForValidation();
    }
}
