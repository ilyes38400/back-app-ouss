<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class MentalPreparationRequest extends FormRequest
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
                    'title'        => 'required|string|max:255',
                    'slug'         => 'required|string|unique:mental_preparations,slug',
                    'description'  => 'nullable|string',
                    'status'       => 'required|in:active,inactive',
                    'program_type' => 'required|in:free,premium,paid',
                    'price'        => 'required_if:program_type,paid|nullable|numeric|min:0',
                    'mental_image' => 'nullable|image',
                    'mental_video' => 'nullable|mimetypes:video/mp4,video/webm,video/ogg|max:51200',
                ];
            case 'patch':
                $id = $this->route('mental-preparation') ?? $this->route('id');
                return [
                    'title'        => 'required|string|max:255',
                    'slug'         => "required|string|unique:mental_preparations,slug,{$id}",
                    'description'  => 'nullable|string',
                    'status'       => 'required|in:active,inactive',
                    'program_type' => 'required|in:free,premium,paid',
                    'price'        => 'required_if:program_type,paid|nullable|numeric|min:0',
                    'mental_image' => 'nullable|image',
                    'mental_video' => 'nullable|mimetypes:video/mp4,video/webm,video/ogg|max:51200',
                ];
            default:
                return [];
        }
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        $data = [
            'status'      => false,
            'message'     => $errors->first(),
            'all_message' => $errors,
        ];
        if ($this->is('api/*') || $this->ajax()) {
            throw new HttpResponseException(response()->json($data, 422));
        }
        throw new HttpResponseException(
            redirect()->back()->withInput()->withErrors($errors)
        );
    }
}
