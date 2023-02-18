<?php

namespace App\Http\Requests\Api\Device;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Auth;

class GetDeviceDetailsRequest extends BaseRequest
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
            'id' => 'nullable|exists:devices,id',
            'device_id' => 'nullable|exists:devices,id',
            'group_id' => 'nullable|exists:groups,id',
        ];
    }
}
