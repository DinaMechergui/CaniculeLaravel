<?php

namespace App\BackOffice\Requests;

use App\Models\CampagneConseil;
use App\Rules\SansChevauchementCampagne;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCampagneConseilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // La case à cocher envoie "1" ou rien -> booléen
        $this->merge([
            'activation_auto' => $this->boolean('activation_auto'),
        ]);
    }

    public function rules(): array
    {
        $statut      = $this->input('statut');
        $conseilId   = $this->input('conseil_id');
        $canal       = $this->input('canal');
        $publicCible = $this->input('public_cible');
        $dateDebut   = $this->input('date_debut');
        $dateFin     = $this->input('date_fin') ?: null;

        $chevauchementRules = [];
        if ($statut === 'active') {
            $chevauchementRules[] = new SansChevauchementCampagne(
                (int) $conseilId,
                (string) $canal,
                (string) $publicCible,
                (string) $dateDebut,
                $dateFin
            );
        }

        return [
            'conseil_id'      => ['required', 'exists:conseils,id'],
            'titre'           => ['required', 'string', 'max:150'],
            'public_cible'    => ['required', Rule::in(array_keys(CampagneConseil::PUBLICS))],
            'canal'           => ['required', Rule::in(array_keys(CampagneConseil::CANAUX))],
            'date_debut'      => ['required', 'date', ...$chevauchementRules],
            'date_fin'        => ['nullable', 'date', 'after:date_debut'],
            'statut'          => ['required', Rule::in(array_keys(CampagneConseil::STATUTS))],
            'activation_auto' => ['boolean'],
            'message'         => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'conseil_id.required' => 'Veuillez choisir un conseil.',
            'conseil_id.exists'   => 'Le conseil sélectionné n\'existe pas.',
            'titre.required'      => 'Le titre est obligatoire.',
            'titre.string'        => 'Le titre doit être un texte.',
            'titre.max'           => 'Le titre ne doit pas dépasser 150 caractères.',
            'public_cible.required' => 'Veuillez choisir un public cible.',
            'public_cible.in'     => 'Le public cible choisi n\'est pas valide.',
            'canal.required'      => 'Veuillez choisir un canal.',
            'canal.in'            => 'Le canal choisi n\'est pas valide.',
            'date_debut.required' => 'La date de début est obligatoire.',
            'date_debut.date'     => 'La date de début n\'est pas une date valide.',
            'date_fin.date'       => 'La date de fin n\'est pas une date valide.',
            'date_fin.after'      => 'La date de fin doit être postérieure à la date de début.',
            'statut.required'     => 'Veuillez choisir un statut.',
            'statut.in'           => 'Le statut choisi n\'est pas valide.',
            'message.max'         => 'Le message ne doit pas dépasser 1 000 caractères.',
        ];
    }
}