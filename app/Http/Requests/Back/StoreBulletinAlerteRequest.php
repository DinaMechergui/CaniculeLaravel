<?php

namespace App\Http\Requests\Back;

use App\Models\Alerte;
use App\Models\BulletinAlerte;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBulletinAlerteRequest extends FormRequest
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
            'alerte_id' => ['required', 'exists:alertes,id'],
            'niveau' => ['required', 'in:vert,jaune,orange,rouge'],
            'evolution' => ['required', 'in:aggravation,stable,amelioration,levee'],
            'message' => ['required', 'string'],
            'conduite_a_tenir' => ['nullable', 'string'],
            'temperature_max' => ['nullable', 'integer', 'between:0,60'],
            'date_publication' => ['required', 'date', 'before_or_equal:now'],
        ];
    }

    /**
     * Custom validation rules and cross-field logic.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $alerteId = $this->input('alerte_id');
            if (!$alerteId) {
                return;
            }

            $alerte = Alerte::find($alerteId);
            if (!$alerte) {
                return;
            }

            // Règle 1 : Impossible d'ajouter un bulletin à une alerte terminée
            if ($alerte->statut === 'terminee') {
                $validator->errors()->add(
                    'alerte_id',
                    'Impossible d\'ajouter un bulletin à une alerte terminée.'
                );
                return;
            }

            // Règle 2 : En cas d'aggravation, le niveau doit être >= niveau du bulletin précédent
            $evolution = $this->input('evolution');
            $niveau = $this->input('niveau');

            if ($evolution === 'aggravation' && $niveau) {
                $dernierBulletin = $alerte->bulletins()->orderBy('date_publication', 'desc')->first();

                if ($dernierBulletin) {
                    $ranks = BulletinAlerte::NIVEAU_RANKS;
                    $currentRank = $ranks[$niveau] ?? 0;
                    $previousRank = $ranks[$dernierBulletin->niveau] ?? 0;

                    if ($currentRank < $previousRank) {
                        $validator->errors()->add(
                            'niveau',
                            "En cas d'aggravation, le niveau de vigilance ({$niveau}) doit être supérieur ou égal à celui du bulletin précédent ({$dernierBulletin->niveau})."
                        );
                    }
                }
            }
        });
    }

    /**
     * Custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'alerte_id' => 'alerte parente',
            'niveau' => 'niveau de vigilance',
            'evolution' => 'évolution de la situation',
            'message' => 'message officiel',
            'conduite_a_tenir' => 'conduite à tenir',
            'temperature_max' => 'température maximale prévue',
            'date_publication' => 'date et heure de publication',
        ];
    }
}
