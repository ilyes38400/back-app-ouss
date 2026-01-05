<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [];
        if(request()->is('api*')) {
            $user_id = auth()->user()->id ?? request()->id;

            // For API registration, we need essential fields
            if(request()->isMethod('post') && request()->is('api/register')) {
                $rules = [
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'email' => 'required|max:191|email|unique:ec_customers,email',
                    'password' => 'required|min:6',
                    'phone_number' => 'nullable|max:20',
                    'user_type' => 'required|string',
                    'status' => 'required|string',
                    'username' => 'required|unique:ec_customers,username',
                    'player_id' => 'nullable|string',
                    'login_type' => 'nullable|string',
                ];

                // Only validate phone uniqueness if it's not empty
                if (!empty(request('phone_number'))) {
                    $rules['phone_number'] .= '|unique:ec_customers,phone_number';
                }
            } else {
                // For other API operations (updates)
                $rules = [
                    'username' => 'required|unique:ec_customers,username,'.$user_id,
                    'email' => 'required|max:191|email|unique:ec_customers,email,'.$user_id,
                    'phone_number' => 'nullable|max:20',
                ];

                // Only validate phone uniqueness if it's not empty
                if (!empty(request('phone_number'))) {
                    $rules['phone_number'] .= '|unique:ec_customers,phone_number,'.$user_id;
                }
            }
        } else {

            $method = strtolower($this->method());
            $user_id = $this->route()->user;

            switch ($method) {
                case 'post':
                    $rules = [
                        'username' => 'required|unique:ec_customers,username',
                        'email' => 'required|max:191|email|unique:ec_customers',
                        'phone_number' => 'nullable|max:20',
                    ];

                    // Only validate phone uniqueness if it's not empty
                    if (!empty(request('phone_number'))) {
                        $rules['phone_number'] .= '|unique:ec_customers,phone_number';
                    }
                break;
                case 'patch':
                    $rules = [
                        'username' => 'required|unique:ec_customers,username,'.$user_id,
                        'email' => 'required|max:191|email|unique:ec_customers,email,'.$user_id,
                        'phone_number' => 'nullable|max:20',
                    ];

                    // Only validate phone uniqueness if it's not empty
                    if (!empty(request('phone_number'))) {
                        $rules['phone_number'] .= '|unique:ec_customers,phone_number,'.$user_id;
                    }
                break;
            }
        }

        return $rules;
    }
    

    public function messages()
    {
        return [
            
        ];
    }

     /**
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator){
        $data = [
            'status' => true,
            'message' => $validator->errors()->first(),
            'all_message' =>  $validator->errors()
        ];

        if ( request()->is('api*')){
           throw new HttpResponseException( response()->json($data,422) );
        }
        if ($this->ajax()) {
            throw new HttpResponseException(response()->json($data,422));
        } else {
            throw new HttpResponseException(redirect()->back()->withInput()->with('errors', $validator->errors()));
        }
    }


}
