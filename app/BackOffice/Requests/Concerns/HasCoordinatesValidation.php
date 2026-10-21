<?php

namespace App\BackOffice\Requests\Concerns;

trait HasCoordinatesValidation
{
    /** @return array<string, array<int, string>> */
    protected function coordinateRules(): array
    {
        return [
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
        ];
    }

    /** @return array<string, string> */
    protected function coordinateMessages(): array
    {
        return [
            'latitude.numeric' => 'La latitude doit être numérique.',
            'latitude.between' => 'La latitude doit être comprise entre -90 et 90.',
            'latitude.required_with' => 'La latitude est obligatoire si la longitude est renseignée.',
            'longitude.numeric' => 'La longitude doit être numérique.',
            'longitude.between' => 'La longitude doit être comprise entre -180 et 180.',
            'longitude.required_with' => 'La longitude est obligatoire si la latitude est renseignée.',
        ];
    }

    protected function prepareCoordinatesForValidation(): void
    {
        $this->merge([
            'latitude' => $this->filled('latitude') ? $this->input('latitude') : null,
            'longitude' => $this->filled('longitude') ? $this->input('longitude') : null,
        ]);
    }
}
