<?php

namespace App\BackOffice\Requests;

use App\Models\Diagnostic;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation de la création d'un diagnostic.
 */
class StoreDiagnosticRequest extends FormRequest
{
    /**
     * Les routes sont déjà protégées par le middleware « admin ».
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Les cases à cocher non cochées ne sont pas envoyées : on les force à false.
     */
    protected function prepareForValidation(): void
    {
        $symptomes = [];
        foreach (array_keys(Diagnostic::SYMPTOMES) as $symptome) {
            $symptomes[$symptome] = $this->boolean($symptome);
        }

        $this->merge($symptomes);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge([
            'signalement_id' => ['required', 'integer', 'exists:signalements,id', 'unique:diagnostics,signalement_id'],
        ], self::reglesCommunes());
    }

    /**
     * Règles partagées avec UpdateDiagnosticRequest et le formulaire du front office.
     *
     * @return array<string, array<mixed>>
     */
    public static function reglesCommunes(): array
    {
        $regles = [
            'zone_touchee' => ['required', 'string', Rule::in(array_keys(Diagnostic::ZONES))],
            'heure_panne'  => ['required', 'string', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'temperature'  => ['nullable', 'numeric', 'between:-10,60'],
            'cause_reelle' => ['nullable', 'string', Rule::in(array_keys(Diagnostic::CAUSES))],
            'remarque'     => ['nullable', 'string', 'max:1000'],
        ];

        foreach (array_keys(Diagnostic::SYMPTOMES) as $symptome) {
            $regles[$symptome] = ['boolean'];
        }

        return $regles;
    }

    /**
     * Messages d'erreur en français.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return self::messagesCommuns();
    }

    /**
     * @return array<string, string>
     */
    public static function messagesCommuns(): array
    {
        return [
            'signalement_id.required' => 'Le signalement est obligatoire.',
            'signalement_id.exists'   => 'Le signalement sélectionné n\'existe pas.',
            'signalement_id.unique'   => 'Ce signalement possède déjà un diagnostic.',
            'zone_touchee.required'   => 'La zone touchée est obligatoire.',
            'zone_touchee.in'         => 'La zone touchée sélectionnée est invalide.',
            'heure_panne.required' => 'L\'heure de la panne est obligatoire.',
            'heure_panne.regex'    => 'L\'heure de la panne doit être au format HH:MM (ex : 14:35).',
            'temperature.numeric'     => 'La température doit être un nombre.',
            'temperature.between'     => 'La température doit être comprise entre -10 et 60 °C.',
            'cause_reelle.in'         => 'La cause sélectionnée est invalide.',
            'remarque.max'            => 'La remarque ne doit pas dépasser 1000 caractères.',
        ];
    }
}
