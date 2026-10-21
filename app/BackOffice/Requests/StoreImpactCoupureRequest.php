<?php

namespace App\BackOffice\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreImpactCoupureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'coupure_id' => ['required', 'integer', 'exists:coupures,id'],
            'nom_structure' => ['required', 'string', 'max:150'],
            'categorie' => ['required', 'in:hopital,ecole,commerce,residence,autre'],
            'adresse' => ['required', 'string', 'max:255'],
            'personnes_affectees' => ['required', 'integer', 'min:0'],
            'niveau_gravite' => ['required', 'in:moyen,eleve,critique'],
            'date_signalement' => ['required', 'date'],
            'mesures_urgence' => ['nullable', 'string'],
            'statut' => ['required', 'in:en_traitement,resolu'],
            'description' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
        ];
    }

    public function messages(): array
    {
        return [
            'coupure_id.required' => 'Veuillez sélectionner une coupure.',
            'coupure_id.exists' => 'La coupure sélectionnée n’existe pas.',
            'nom_structure.required' => 'Le nom de la structure est obligatoire.',
            'nom_structure.max' => 'Le nom de la structure ne peut pas dépasser 150 caractères.',
            'categorie.required' => 'La catégorie de la structure est obligatoire.',
            'categorie.in' => 'La catégorie sélectionnée est invalide.',
            'adresse.required' => 'L’adresse de la structure est obligatoire.',
            'adresse.max' => 'L’adresse ne peut pas dépasser 255 caractères.',
            'personnes_affectees.required' => 'Le nombre de personnes affectées est obligatoire.',
            'personnes_affectees.integer' => 'Le nombre de personnes doit être un entier.',
            'personnes_affectees.min' => 'Le nombre de personnes affectées ne peut pas être négatif.',
            'niveau_gravite.required' => 'Le niveau de gravité est obligatoire.',
            'niveau_gravite.in' => 'Le niveau de gravité sélectionné est invalide.',
            'date_signalement.required' => 'La date du signalement est obligatoire.',
            'date_signalement.date' => 'La date du signalement est invalide.',
            'statut.required' => 'Le statut de l’impact est obligatoire.',
            'statut.in' => 'Le statut sélectionné est invalide.',
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
