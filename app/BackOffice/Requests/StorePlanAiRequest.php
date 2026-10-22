<?php
namespace App\BackOffice\Requests;
use App\Models\PlanUrgence;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StorePlanAiRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        if ($this->has('titre') && is_string($this->input('titre')) && mb_strlen($this->input('titre')) > 150) {
            $this->merge(['titre' => mb_substr(trim($this->input('titre')), 0, 150)]);
        }
        if ($this->has('description') && is_string($this->input('description')) && mb_strlen($this->input('description')) > 1000) {
            $this->merge(['description' => mb_substr(trim($this->input('description')), 0, 1000)]);
        }
        if ($this->has('etapes') && is_array($this->input('etapes'))) {
            $etapes = $this->input('etapes');
            foreach ($etapes as &$step) {
                if (isset($step['titre']) && is_string($step['titre']) && mb_strlen($step['titre']) > 150) {
                    $step['titre'] = mb_substr(trim($step['titre']), 0, 150);
                }
                if (isset($step['description']) && is_string($step['description']) && mb_strlen($step['description']) > 500) {
                    $step['description'] = mb_substr(trim($step['description']), 0, 500);
                }
                if (isset($step['moment']) && is_string($step['moment'])) {
                    $m = mb_strtolower(trim($step['moment']));
                    if (str_contains($m, 'av')) {
                        $step['moment'] = 'avant';
                    } elseif (str_contains($m, 'ap')) {
                        $step['moment'] = 'apres';
                    } else {
                        $step['moment'] = 'pendant';
                    }
                }
                if (isset($step['obligatoire'])) {
                    $step['obligatoire'] = filter_var($step['obligatoire'], FILTER_VALIDATE_BOOLEAN);
                }
            }
            $this->merge(['etapes' => $etapes]);
        }
    }

    public function rules(): array
    {
        return [
            'titre' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type_evenement' => ['required', Rule::in(array_keys(PlanUrgence::TYPES))],
            'niveau_risque' => ['required', Rule::in(array_keys(PlanUrgence::NIVEAUX))],
            'public_cible' => ['required', Rule::in(array_keys(PlanUrgence::PUBLICS))],
            'etapes' => ['required', 'array', 'min:3', 'max:25'],
            'etapes.*.titre' => ['required', 'string', 'max:150'],
            'etapes.*.description' => ['nullable', 'string', 'max:500'],
            'etapes.*.moment' => ['required', Rule::in(['avant', 'pendant', 'apres'])],
            'etapes.*.obligatoire' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'titre.required' => 'Le titre du plan est obligatoire.',
            'titre.max' => 'Le titre ne peut dépasser 150 caractères.',
            'type_evenement.in' => 'Le type d’événement est invalide.',
            'niveau_risque.in' => 'Le niveau de risque est invalide.',
            'public_cible.in' => 'Le public cible est invalide.',
            'etapes.min' => 'Un plan IA doit contenir au moins trois étapes.',
            'etapes.max' => 'Un plan IA ne peut dépasser vingt-cinq étapes.',
            'etapes.*.titre.required' => 'Chaque étape doit avoir un titre.',
            'etapes.*.moment.in' => 'Le moment d’une étape est invalide.',
        ];
    }
}
