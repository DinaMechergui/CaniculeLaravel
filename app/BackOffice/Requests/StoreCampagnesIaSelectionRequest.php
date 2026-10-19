<?php
namespace App\BackOffice\Requests;
use App\Models\CampagneConseil;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreCampagnesIaSelectionRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['campagnes'=>['required','array','min:1','max:5'],'campagnes.*.conseil_id'=>['required','integer','exists:conseils,id'],'campagnes.*.titre'=>['required','string','max:150'],'campagnes.*.message'=>['required','string','max:1000'],'campagnes.*.public_cible'=>['required',Rule::in(array_keys(CampagneConseil::PUBLICS))],'campagnes.*.canal'=>['required',Rule::in(array_keys(CampagneConseil::CANAUX))],'campagnes.*.date_debut'=>['required','date'],'campagnes.*.date_fin'=>['nullable','date','after:campagnes.*.date_debut']]; }
    public function messages(): array { return ['campagnes.required'=>'Sélectionnez au moins un brouillon.','campagnes.max'=>'Vous pouvez créer cinq brouillons au maximum.','campagnes.*.conseil_id.exists'=>'Un conseil de la sélection est introuvable.','campagnes.*.titre.required'=>'Le titre est obligatoire pour chaque brouillon.','campagnes.*.message.required'=>'Le message est obligatoire pour chaque brouillon.','campagnes.*.public_cible.*'=>'Un public cible sélectionné est invalide.','campagnes.*.canal.*'=>'Un canal sélectionné est invalide.','campagnes.*.date_debut.required'=>'La date de début est obligatoire.','campagnes.*.date_fin.after'=>'La date de fin doit suivre la date de début.']; }
}
