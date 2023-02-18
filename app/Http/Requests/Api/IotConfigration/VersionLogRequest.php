<?php

namespace App\Http\Requests\Api\IotConfigration;

use App\Http\Requests\BaseRequest;

class VersionLogRequest extends BaseRequest
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
            'mac_address' => 'required',
            'version' => 'required',
        ];
    }
}
