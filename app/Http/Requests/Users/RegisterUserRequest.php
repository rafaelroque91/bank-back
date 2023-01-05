<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\ValidationException;

use Illuminate\Contracts\Validation\Validator;

class RegisterUserRequest extends FormRequest
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
        return [
            'email'=>'required|email|unique:users',
            'username' => 'required|string|unique:users',
            'password'=>'required|string'
        ];
    }
}
