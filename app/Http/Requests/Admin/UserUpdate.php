<?php

namespace App\Http\Requests\Admin;

use App\Services\Plugin\HookManager;
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
        $rules = [
            'id' => 'required|integer',
            'email' => 'email:strict',
            'password' => 'nullable|min:8',
            'transfer_enable' => 'numeric',
            'expired_at' => 'nullable|integer',
            'banned' => 'bool',
            'plan_id' => 'nullable|integer',
            'commission_rate' => 'nullable|integer|min:0|max:100',
            'discount' => 'nullable|integer|min:0|max:100',
            'is_admin' => 'boolean',
            'is_staff' => 'boolean',
            'u' => 'integer',
            'd' => 'integer',
            'balance' => 'numeric',
            'commission_type' => 'integer',
            'commission_balance' => 'numeric',
            'remarks' => 'nullable',
            'speed_limit' => 'nullable|integer',
            'device_limit' => 'nullable|integer'
        ];

        return HookManager::filter('admin.user.update.rules', $rules, $this);
    }

    public function messages()
    {
        $messages = [
            'email.required' => 'Email cannot be empty',
            'email.email' => 'Incorrect email format',
            'transfer_enable.numeric' => 'Incorrect traffic format',
            'expired_at.integer' => 'Incorrect expiration time format',
            'banned.in' => 'Incorrect ban status format',
            'is_admin.required' => 'Administrator status cannot be empty',
            'is_admin.in' => 'Incorrect administrator status format',
            'is_staff.required' => 'Employee status cannot be empty',
            'is_staff.in' => 'Incorrect employee status format',
            'plan_id.integer' => 'Incorrect subscription plan format',
            'commission_rate.integer' => 'Incorrect recommended rebate ratio format',
            'commission_rate.nullable' => 'Incorrect recommended rebate ratio format',
            'commission_rate.min' => 'Recommended rebate ratio minimum is 0',
            'commission_rate.max' => 'Recommended rebate ratio maximum is 100',
            'discount.integer' => 'Exclusive discount ratio format incorrect',
            'discount.nullable' => 'Exclusive discount ratio format incorrect',
            'discount.min' => 'Exclusive discount ratio minimum is 0',
            'discount.max' => 'Exclusive discount ratio maximum is 100',
            'u.integer' => 'Incorrect upload traffic format',
            'd.integer' => 'Incorrect download traffic format',
            'balance.integer' => 'Incorrect balance format',
            'commission_balance.integer' => 'Incorrect commission format',
            'password.min' => 'Minimum password length is 8 characters',
            'speed_limit.integer' => 'Incorrect speed limit format',
            'device_limit.integer' => 'Incorrect device quantity format'
        ];

        return HookManager::filter('admin.user.update.messages', $messages, $this);
    }
}
