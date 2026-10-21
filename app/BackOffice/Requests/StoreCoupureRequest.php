<?php

namespace App\BackOffice\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCoupureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'quartier_id' => ['required', 'exists:quartiers,id'],
            'type' => ['required', 'in:delestage,panne,surcharge'],
            'statut' => ['required', 'in:prevue,en_cours,terminee'],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['nullable', 'date', 'after:date_debut'],
            'description' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
        ];
    }

    public function messages(): array
    {
        return [
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
            'latitude.numeric' => 'La latitude doit être numérique.',
            'latitude.between' => 'La latitude doit être comprise entre -90 et 90.',
            'latitude.required_with' => 'La latitude est obligatoire si la longitude est renseignée.',
            'longitude.numeric' => 'La longitude doit être numérique.',
            'longitude.between' => 'La longitude doit être comprise entre -180 et 180.',
            'longitude.required_with' => 'La longitude est obligatoire si la latitude est renseignée.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'latitude' => $this->filled('latitude') ? $this->input('latitude') : null,
            'longitude' => $this->filled('longitude') ? $this->input('longitude') : null,
        ]);
    }
}
