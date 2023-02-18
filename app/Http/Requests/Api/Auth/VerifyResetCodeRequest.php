<?php

namespace App\Http\Requests\Api\Auth;

use App\Http\Requests\BaseRequest;

class VerifyResetCodeRequest extends BaseRequest
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
            'email' => 'required|email:dns,rfc',
            'verification_code' => 'required',
            'uuid' => 'nullable',
            'token' => 'nullable',
            'type' => 'nullable|in:' . DEVICE_ANDROID . ',' . DEVICE_IOS
        ];
    }
}
