<?php

namespace App\Http\Requests\Api\Schedule;

use App\Http\Requests\BaseRequest;
use App\Rules\ValidAquariumRule;
use App\Rules\ValidDeviceIdRule;
use App\Rules\ValidGroupIdRule;

class ListingRequest extends BaseRequest
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
            'aquarium_id' => ['nullable', 'integer', 'exists:aquaria,id', new ValidAquariumRule()],
            'device_id' => ['nullable', 'integer', 'exists:devices,id', new ValidDeviceIdRule()],
            'group_id' => ['nullable','integer','exists:groups,id', new ValidGroupIdRule()],
        ];
    }
}
