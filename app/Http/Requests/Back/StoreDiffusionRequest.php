<?php

namespace App\Http\Requests\Back;

use Illuminate\Foundation\Http\FormRequest;

class StoreDiffusionRequest extends FormRequest
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
            'bulletin_alerte_id' => ['required', 'exists:bulletins_alerte,id'],
            'canal' => ['required', 'in:sms,email,notification_push,sirene,affichage'],
            'contenu' => ['required', 'string', 'max:160'],
            'destinataires_estimes' => ['required', 'integer', 'min:0'],
            'date_envoi' => ['required', 'date'],
            'statut' => ['required', 'in:planifiee,envoyee,echec'],
        ];
    }

    /**
     * Custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'bulletin_alerte_id' => 'bulletin d\'alerte',
            'canal' => 'canal de diffusion',
            'contenu' => 'message court diffusé',
            'destinataires_estimes' => 'nombre de destinataires estimés',
            'date_envoi' => 'date et heure d\'envoi',
            'statut' => 'statut de la diffusion',
        ];
    }
}
