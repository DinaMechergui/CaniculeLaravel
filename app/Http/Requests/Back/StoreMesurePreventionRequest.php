<?php

namespace App\Http\Requests\Back;

use Illuminate\Foundation\Http\FormRequest;

class StoreMesurePreventionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'alerte_meteo_id' => ['required', 'exists:alertes_meteo,id'],
            'titre' => ['required', 'string', 'max:150'],
            'type' => ['required', 'in:evacuation,fermeture_site,distribution_eau,renfort_secours'],
            'description' => ['required', 'string'],
            'responsable' => ['required', 'string', 'max:100'],
            'date_debut' => ['required', 'date'],
            'statut' => ['required', 'in:prevue,en_cours,terminee'],
        ];
    }

    /**
     * Messages d'erreur personnalisés.
     */
    public function messages(): array
    {
        return [
            'alerte_meteo_id.required' => 'L\'alerte météo associée est obligatoire.',
            'alerte_meteo_id.exists' => 'L\'alerte météo sélectionnée n\'existe pas.',
            'titre.required' => 'Le titre de la mesure est obligatoire.',
            'titre.max' => 'Le titre ne doit pas dépasser 150 caractères.',
            'type.required' => 'Le type de mesure est obligatoire.',
            'type.in' => 'Le type sélectionné est invalide.',
            'description.required' => 'La description est obligatoire.',
            'responsable.required' => 'Le responsable est obligatoire.',
            'responsable.max' => 'Le nom du responsable ne doit pas dépasser 100 caractères.',
            'date_debut.required' => 'La date et heure de début est obligatoire.',
            'date_debut.date' => 'La date de début doit être une date valide.',
            'statut.required' => 'Le statut de la mesure est obligatoire.',
            'statut.in' => 'Le statut sélectionné est invalide.',
        ];
    }
}
