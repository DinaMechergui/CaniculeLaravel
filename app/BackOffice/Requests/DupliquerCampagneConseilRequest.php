<?php

namespace App\BackOffice\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DupliquerCampagneConseilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'decalage_jours' => ['nullable', 'integer', 'min:1', 'max:365'],
        ];
    }

    public function messages(): array
    {
        return [
            'decalage_jours.integer' => 'Le décalage doit être un nombre entier.',
            'decalage_jours.min'     => 'Le décalage doit être d\'au moins 1 jour.',
            'decalage_jours.max'     => 'Le décalage ne peut pas dépasser 365 jours.',
        ];
    }
}
