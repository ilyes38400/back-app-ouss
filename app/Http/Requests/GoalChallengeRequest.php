<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GoalChallengeRequest extends FormRequest
{
    /**
     * Détermine si l’utilisateur est autorisé à faire cette requête.
     *
     * @return bool
     */
    public function authorize()
    {
        // Vous pouvez ajouter ici une vérification de permission, par exemple :
        // return auth()->user()->can('goal_challenge-add') || auth()->user()->can('goal_challenge-edit');
        return true;
    }

    /**
     * Règles de validation pour store (POST) et update (PATCH).
     *
     * @return array
     */
    public function rules()
    {
        $method = strtolower($this->method());

        switch ($method) {
            case 'post':
                return [
                    'title'       => 'required|string|max:255',
                    'theme'       => 'required|in:physique,alimentaire,mental',
                    'description' => 'nullable|string',
                    'status'      => 'required|in:active,inactive',
                    'image'       => 'nullable|image',
                ];

            case 'patch':
                return [
                    'title'       => 'required|string|max:255',
                    'theme'       => 'required|in:physique,alimentaire,mental',
                    'description' => 'nullable|string',
                    'status'      => 'required|in:active,inactive',
                    'image'       => 'nullable|image',
                ];

            default:
                return [];
        }
    }

    /**
     * Messages d’erreur personnalisés (facultatif).
     *
     * @return array
     */
    public function messages()
    {
        return [
            // 'title.required' => 'Le titre est obligatoire.',
            // 'theme.in'       => 'Le thème sélectionné n’est pas valide.',
            // 'status.in'      => 'Le statut doit être "active" ou "inactive".',
            // etc.
        ];
    }

    /**
     * Gère l’échec de validation.
     *
     * @param  Validator  $validator
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        $data = [
            'status'      => false,
            'message'     => $validator->errors()->first(),
            'all_message' => $validator->errors(),
        ];

        if ($this->is('api/*')) {
            throw new HttpResponseException(response()->json($data, 422));
        }

        if ($this->ajax()) {
            throw new HttpResponseException(response()->json($data, 422));
        }

        throw new HttpResponseException(
            redirect()->back()
                      ->withInput()
                      ->with('errors', $validator->errors())
        );
    }
}
