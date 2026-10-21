<?php

namespace App\BackOffice\Requests;

use Illuminate\Validation\Rule;

class UpdateQuartierRequest extends StoreQuartierRequest
{
    /** @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['nom'] = ['required', 'string', 'max:100', Rule::unique('quartiers', 'nom')->ignore($this->route('quartier'))];

        return $rules;
    }
}
