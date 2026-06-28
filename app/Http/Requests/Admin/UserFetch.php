<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UserFetch extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'filter.*.key' => 'required|in:id,email,transfer_enable,d,expired_at,uuid,token,invite_by_email,invite_user_id,plan_id,banned,remarks,is_admin',
            'filter.*.condition' => 'required|in:>,<,=,>=,<=,fuzzy,!=',
            'filter.*.value' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'filter.*.key.required' => 'The filter key cannot be empty.',
            'filter.*.key.in' => 'There is an error in the filter key parameter.',
            'filter.*.condition.required' => 'The filter condition cannot be empty.',
            'filter.*.condition.in' => 'There is an error in the filter condition parameter.',
            'filter.*.value.required' => 'The filter value cannot be empty.'
        ];
    }
}
