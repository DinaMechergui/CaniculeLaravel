<?php

namespace App\BackOffice\Requests;

use Illuminate\Validation\Rule;

/**
 * Validation de la modification d'un diagnostic.
 */
class UpdateDiagnosticRequest extends StoreDiagnosticRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge([
            'signalement_id' => [
                'required', 'integer', 'exists:signalements,id',
                // Le signalement actuel du diagnostic reste autorisé.
                Rule::unique('diagnostics', 'signalement_id')->ignore($this->route('diagnostic')),
            ],
        ], self::reglesCommunes());
    }
}
