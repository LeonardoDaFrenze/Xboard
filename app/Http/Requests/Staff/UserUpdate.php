<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdate extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|email:strict',
            'password' => 'nullable',
            'transfer_enable' => 'numeric',
            'expired_at' => 'nullable|integer',
            'banned' => 'required|in:0,1',
            'plan_id' => 'nullable|integer',
            'commission_rate' => 'nullable|integer|min:0|max:100',
            'discount' => 'nullable|integer|min:0|max:100',
            'u' => 'integer',
            'd' => 'integer',
            'balance' => 'integer',
            'commission_balance' => 'integer'
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Email cannot be empty',
            'email.email' => 'Incorrect email format',
            'transfer_enable.numeric' => 'Incorrect traffic format',
            'expired_at.integer' => 'Incorrect expiration time format',
            'banned.required' => 'Whether to ban cannot be empty',
            'banned.in' => 'Incorrect format for whether to ban',
            'plan_id.integer' => 'Incorrect subscription plan format',
            'commission_rate.integer' => 'Incorrect recommended bonus ratio format',
            'commission_rate.nullable' => 'Incorrect recommended bonus ratio format',
            'commission_rate.min' => 'Recommended bonus ratio minimum is 0',
            'commission_rate.max' => 'Recommended bonus ratio maximum is 100',
            'discount.integer' => 'Exclusive discount ratio format incorrect',
            'discount.nullable' => 'Exclusive discount ratio format incorrect',
            'discount.min' => 'Exclusive discount ratio minimum is 0',
            'discount.max' => 'Exclusive discount ratio maximum is 100',
            'u.integer' => 'Incorrect upload traffic format',
            'd.integer' => 'Incorrect download traffic format',
            'balance.integer' => 'Incorrect balance format',
            'commission_balance.integer' => 'Incorrect commission format'
        ];
    }
}
