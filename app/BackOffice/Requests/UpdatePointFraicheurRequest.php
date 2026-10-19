<?php

namespace App\BackOffice\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation de la modification d'un point de fraîcheur.
 */
class UpdatePointFraicheurRequest extends FormRequest
{
    /**
     * Les routes sont déjà protégées par le middleware « admin ».
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * La case « accessible » peut être décochée : on la transforme
     * systématiquement en booléen avant la validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'accessible' => $this->boolean('accessible'),
        ]);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type_point_id' => ['required', 'exists:type_points,id'],
            'nom' => ['required', 'string', 'max:150'],
            'adresse' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'horaires' => ['nullable', 'string', 'max:100'],
            'accessible' => ['boolean'],
            // Facultatif pour rester rétro-compatible : sans valeur, la
            // capacité existante du point est conservée.
            'capacite' => ['sometimes', 'required', 'integer', 'min:1', 'max:10000'],
        ];
    }

    /**
     * Messages d'erreur en français.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type_point_id.required' => 'Le type de point est obligatoire.',
            'type_point_id.exists' => 'Le type de point sélectionné n\'existe pas.',
            'nom.required' => 'Le nom du point est obligatoire.',
            'nom.string' => 'Le nom du point doit être une chaîne de caractères.',
            'nom.max' => 'Le nom du point ne doit pas dépasser 150 caractères.',
            'adresse.required' => 'L\'adresse est obligatoire.',
            'adresse.string' => 'L\'adresse doit être une chaîne de caractères.',
            'adresse.max' => 'L\'adresse ne doit pas dépasser 255 caractères.',
            'latitude.required' => 'La latitude est obligatoire.',
            'latitude.numeric' => 'La latitude doit être un nombre.',
            'latitude.between' => 'La latitude doit être comprise entre -90 et 90.',
            'longitude.required' => 'La longitude est obligatoire.',
            'longitude.numeric' => 'La longitude doit être un nombre.',
            'longitude.between' => 'La longitude doit être comprise entre -180 et 180.',
            'horaires.string' => 'Les horaires doivent être une chaîne de caractères.',
            'horaires.max' => 'Les horaires ne doivent pas dépasser 100 caractères.',
            'accessible.boolean' => 'Le champ accessible doit être vrai ou faux.',
            'capacite.required' => 'La capacité est obligatoire.',
            'capacite.integer' => 'La capacité doit être un nombre entier.',
            'capacite.min' => 'La capacité doit être au minimum de 1 personne.',
            'capacite.max' => 'La capacité ne doit pas dépasser 10 000 personnes.',
        ];
    }
}
