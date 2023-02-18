<?php

namespace App\Http\Requests\Api\Auth;

use App\Http\Requests\BaseRequest;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends BaseRequest
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
            'first_name' => 'required_unless:login_type,'.LOGIN_APPLE.'|min:3|max:200',
            'email' => 'required_unless:login_type,'.LOGIN_APPLE.'|email:rfc,dns',
            'password' => 'required_if:login_type,' . LOGIN_EMAIL . '|min:6',
            'social_user_id' => 'required_if:login_type,' . LOGIN_GOOGLE . ',' . LOGIN_FACEBOOK . ',' . LOGIN_APPLE,
            'social_token' => 'required_if:login_type,' . LOGIN_GOOGLE . ',' . LOGIN_FACEBOOK,
            'phone_no' => 'nullable',
            'tank_size' => 'required_if:login_type,' . LOGIN_EMAIL,
            'login_type' => 'required|in:' . LOGIN_EMAIL . ',' . LOGIN_GOOGLE . ',' . LOGIN_FACEBOOK . ',' . LOGIN_APPLE
        ];
    }
}
