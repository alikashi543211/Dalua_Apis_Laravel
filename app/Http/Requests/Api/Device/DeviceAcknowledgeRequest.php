<?php

namespace App\Http\Requests\Api\Device;

use App\Http\Requests\BaseRequest;

class DeviceAcknowledgeRequest extends  BaseRequest
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
            'topic' => 'required',
            'timestamp' => 'required|numeric',
            'command_id' => 'required|integer',
            'response' => 'required|json',
            'device_id' => 'prohibits:group_id|exists:devices,id',
            'group_id' => 'prohibits:device_id|exists:groups,id'
        ];
    }

    public function messages()
    {
        return [
            'device_id.prohibits' => 'Group id is not allowed when device id is present',
            'group_id.prohibits' => 'Device id is not allowed when group id is present'
        ];
    }
}
