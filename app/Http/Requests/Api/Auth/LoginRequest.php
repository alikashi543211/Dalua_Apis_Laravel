<?php

namespace App\Http\Requests\Api\Auth;

use App\Http\Requests\BaseRequest;

class LoginRequest extends BaseRequest
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
            'username' => 'required',
            'password' => 'required_if:login_type,' . LOGIN_EMAIL,
            'login_type' => 'required|in:' . LOGIN_EMAIL . ',' . LOGIN_GOOGLE . ',' . LOGIN_FACEBOOK . ',' . LOGIN_APPLE,
            'uuid' => 'nullable',
            'token' => 'nullable',
            'type' => 'nullable|in:' . DEVICE_ANDROID . ',' . DEVICE_IOS

        ];
    }
}
