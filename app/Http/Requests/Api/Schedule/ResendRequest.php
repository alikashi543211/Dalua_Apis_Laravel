<?php

namespace App\Http\Requests\Api\Schedule;

use App\Http\Requests\BaseRequest;
use App\Rules\ValidDeviceIdRule;
use App\Rules\ValidGroupIdRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ResendRequest extends BaseRequest
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
            'device_id' => ['integer', 'exists:devices,id', new ValidDeviceIdRule()],
            'group_id' => ['integer','exists:groups,id', new ValidGroupIdRule()],
        ];
    }
}
