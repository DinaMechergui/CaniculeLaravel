<?php

namespace App\BackOffice\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActivateCampagneConseilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return []; // Validation métier gérée dans CampagneService::activer()
    }
}
