<?php

namespace App\Http\Requests\Api\Device;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Auth;

class CheckMacAddressesRequest extends BaseRequest
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
            'mac_addresses' => 'required|array',
            'mac_addresses.*' => 'min:1',
        ];
    }
}
