<?php
namespace App\BackOffice\Requests;

use App\Models\CampagneConseil;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CampagneAiRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        $action=$this->input('action');
        return [
            'action' => ['required', Rule::in(['rediger','reformuler','traduire','diffusion','qualite','analyse','propositions'])],
            'conseil_id' => [$action==='rediger'||$action==='diffusion'?'required':'nullable','integer','exists:conseils,id'],
            'titre' => ['nullable','string','max:150'],
            'contenu' => ['nullable','string','max:10000'],
            'message' => [in_array($action,['reformuler','traduire','qualite'],true)?'required':'nullable','string','max:1000'],
            'canal' => ['nullable',Rule::in(array_keys(CampagneConseil::CANAUX))],
            'public_cible' => ['nullable',Rule::in(array_keys(CampagneConseil::PUBLICS))],
            'langue' => [$action==='traduire'?'required':'nullable',Rule::in(['ar','en'])],
        ];
    }
    public function messages(): array
    {
        return ['action.required'=>'Choisissez une action IA.','action.in'=>'Cette action IA est inconnue.','conseil_id.required'=>'Choisissez un conseil.','conseil_id.exists'=>'Le conseil sélectionné est introuvable.','titre.max'=>'Le titre ne peut dépasser 150 caractères.','message.required'=>'Saisissez un message à traiter.','message.max'=>'Le message ne peut dépasser 1 000 caractères.','langue.required'=>'Choisissez une langue.','langue.in'=>'Choisissez l’arabe ou l’anglais.','canal.in'=>'Le canal choisi est invalide.','public_cible.in'=>'Le public choisi est invalide.'];
    }
}
