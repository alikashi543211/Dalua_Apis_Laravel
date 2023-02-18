<?php

namespace App\Http\Requests\Api\Auth;

use App\Http\Requests\BaseRequest;

class VerifyEmailVerificationCodeRequest extends BaseRequest
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
            'email' => 'required|exists:users,email,role_id,' . USER_APP,
            'verification_code' => 'required|exists:users,verification_code',
            'uuid' => 'nullable',
            'token' => 'nullable',
            'type' => 'nullable|in:' . DEVICE_ANDROID . ',' . DEVICE_IOS
        ];
    }
}
