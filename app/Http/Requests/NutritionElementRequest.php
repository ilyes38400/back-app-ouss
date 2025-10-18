<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class NutritionElementRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $method = strtolower($this->method());

        switch ($method) {
            case 'post':
                return [
                    'title'       => 'required|string|max:255',
                    'slug'        => 'required|string|unique:nutrition_elements,slug',
                    'description' => 'nullable|string',
                    'status'      => 'required|in:active,inactive',
                    'image'       => 'nullable|image',
                ];

            case 'patch':
                // Récupère l'ID correctement
                $id = $this->route('nutrition_element');

                return [
                    'title'       => 'required|string|max:255',
                    'slug'        => [
                        'required',
                        'string',
                        Rule::unique('nutrition_elements', 'slug')->ignore($id),
                    ],
                    'description' => 'nullable|string',
                    'status'      => 'required|in:active,inactive',
                    'image'       => 'nullable|image',
                ];

            default:
                return [];
        }
    }

    public function messages()
    {
        return [
            'slug.unique' => __('Le slug est déjà utilisé pour un autre élément.'),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $data = [
            'status'      => false,
            'message'     => $validator->errors()->first(),
            'all_message' => $validator->errors(),
        ];

        if ($this->is('api/*') || $this->ajax()) {
            throw new HttpResponseException(response()->json($data, 422));
        }

        throw new HttpResponseException(
            redirect()->back()->withInput()->with('errors', $validator->errors())
        );
    }
}
