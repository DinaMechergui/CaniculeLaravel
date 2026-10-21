<?php

namespace App\Http\Requests\Back;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAlerteRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'titre' => ['required', 'string', 'max:150'],
            'type' => [
                'required',
                'in:canicule,orage,inondation,vent_fort,incendie_foret,pollution_air',
            ],
            'zone_concernee' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string'],
            'source' => ['nullable', 'string', 'max:100'],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['nullable', 'date', 'after:date_debut'],
            'statut' => ['required', 'in:en_cours,terminee'],
        ];
    }

    /**
     * Custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'titre' => 'titre de l\'alerte',
            'type' => 'type d\'aléa',
            'zone_concernee' => 'zone géographique concernée',
            'description' => 'description détaillée',
            'source' => 'organisme émetteur',
            'date_debut' => 'date et heure de début',
            'date_fin' => 'date et heure de fin',
            'statut' => 'statut de l\'alerte',
        ];
    }
}
