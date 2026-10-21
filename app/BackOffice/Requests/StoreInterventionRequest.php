<?php

namespace App\BackOffice\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreInterventionRequest extends FormRequest
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
            'equipe' => ['required', 'string', 'max:100'],
            'technicien' => ['required', 'string', 'max:100'],
            'date_intervention' => ['required', 'date'],
            'duree_estimee' => ['required', 'integer', 'min:1'],
            'statut' => ['required', 'in:prevue,en_cours,terminee'],
            'rapport' => ['nullable', 'string', 'required_if:statut,terminee'],
        ];
    }

    public function messages(): array
    {
        return [
            'coupure_id.required' => 'Veuillez sélectionner une coupure.',
            'coupure_id.exists' => 'La coupure sélectionnée n’existe pas.',
            'equipe.required' => 'Le nom de l’équipe est obligatoire.',
            'equipe.max' => 'Le nom de l’équipe ne peut pas dépasser 100 caractères.',
            'technicien.required' => 'Le nom du technicien est obligatoire.',
            'technicien.max' => 'Le nom du technicien ne peut pas dépasser 100 caractères.',
            'date_intervention.required' => 'La date de l’intervention est obligatoire.',
            'date_intervention.date' => 'La date de l’intervention est invalide.',
            'duree_estimee.required' => 'La durée estimée est obligatoire.',
            'duree_estimee.integer' => 'La durée doit être exprimée en minutes entières.',
            'duree_estimee.min' => 'La durée estimée doit être supérieure ou égale à une minute.',
            'statut.required' => 'Le statut de l’intervention est obligatoire.',
            'statut.in' => 'Le statut sélectionné est invalide.',
            'rapport.required_if' => 'Le rapport technique est obligatoire pour une intervention terminée.',
        ];
    }
}
