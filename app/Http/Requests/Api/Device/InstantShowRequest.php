<?php

namespace App\Http\Requests\Api\Device;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Auth;

class InstantShowRequest extends BaseRequest
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
            'uid' => 'required',
            'value_a' => 'required|integer|min:0|max:100',
            'value_b' => 'required|integer|min:0|max:100',
            'value_c' => 'required|integer|min:0|max:100',
            'master_control' => 'required|integer|min:0|max:100',
        ];
    }
}
