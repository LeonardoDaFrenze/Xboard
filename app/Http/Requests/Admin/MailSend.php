<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MailSend extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type' => 'required|in:1,2,3,4',
            'subject' => 'required',
            'content' => 'required',
            'receiver' => 'array'
        ];
    }

    public function messages()
    {
        return [
            'type.required' => 'The sending type cannot be empty.',
            'type.in' => 'The format of the sending type is incorrect.',
            'subject.required' => 'The subject cannot be empty.',
            'content.required' => 'The content cannot be empty.',
            'receiver.array' => 'The recipient's format is incorrect.'
        ];
    }
}
