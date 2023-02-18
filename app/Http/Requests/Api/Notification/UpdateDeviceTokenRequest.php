<?php

namespace App\Http\Requests\Api\Notification;

use App\Http\Requests\BaseRequest;

class UpdateDeviceTokenRequest extends BaseRequest
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
            'uuid' => 'required',
            'token' => 'required',
            'type' => 'required|in:' . DEVICE_ANDROID . ',' . DEVICE_IOS
        ];
    }
}
