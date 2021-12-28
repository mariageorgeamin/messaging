<?php

namespace App\Http\Validators;

class CreateMessageValidator extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'subject' => 'required',
            'message' => 'required',
            'recipients' => 'required',
            ];
    }
}
