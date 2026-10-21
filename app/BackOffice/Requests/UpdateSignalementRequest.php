<?php

namespace App\BackOffice\Requests;

use App\Models\Signalement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation de la modification d'un signalement.
 */
class UpdateSignalementRequest extends FormRequest
{
    /**
     * Les routes sont déjà protégées par le middleware « admin ».
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type_signalement_id' => ['required', 'integer', 'exists:type_signalements,id'],
            'description'         => ['required', 'string', 'max:1000'],
            'statut'              => ['required', 'string', Rule::in(array_keys(Signalement::STATUTS))],
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
            'type_signalement_id.required' => 'Le type de signalement est obligatoire.',
            'type_signalement_id.exists'   => 'Le type de signalement sélectionné n\'existe pas.',
            'description.required'         => 'La description est obligatoire.',
            'description.max'              => 'La description ne doit pas dépasser 1000 caractères.',
            'statut.required'              => 'Le statut est obligatoire.',
            'statut.in'                    => 'Le statut sélectionné est invalide.',
        ];
    }
}
