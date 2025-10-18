<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class HomeInformationRequest extends FormRequest
{
    /**
     * Autorise toujours : la vérification des permissions
     * se fait dans le contrôleur.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Règles de validation selon la méthode HTTP.
     */
    public function rules()
    {
        $method = strtolower($this->method());

        switch ($method) {
            case 'post':
                return [
                    'title'      => 'required|string|max:255',
                    'home_video' => 'nullable|mimetypes:video/mp4,video/webm,video/ogg|max:51200',
                ];

            case 'put':
            case 'patch':
                // on récupère l’identifiant via le paramètre de route
                $id = $this->route('home-information') 
                      ?? $this->route('id');

                return [
                    'title'      => 'required|string|max:255',
                    'home_video' => 'nullable|mimetypes:video/mp4,video/webm,video/ogg|max:51200',
                ];

            default:
                return [];
        }
    }

    /**
     * Libellés personnalisés pour les attributs (dans les messages d’erreur).
     */
    public function attributes()
    {
        return [
            'title'      => __('message.title'),
            'home_video' => __('message.video'),
        ];
    }

    /**
     * Si la validation échoue :
     * - on renvoie JSON pour API/AJAX,
     * - sinon on redirige en arrière avec les erreurs.
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        $payload = [
            'message'     => $errors->first(),
            'all_message' => $errors,
        ];

        if ($this->is('api/*') || $this->ajax()) {
            throw new HttpResponseException(
                response()->json($payload, 422)
            );
        }

        throw new HttpResponseException(
            redirect()->back()
                     ->withInput()
                     ->withErrors($errors)
        );
    }
}
