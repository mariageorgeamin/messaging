<?php

namespace App\Http\Validators;

class RegisterValidator extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
            ];
    }
}
