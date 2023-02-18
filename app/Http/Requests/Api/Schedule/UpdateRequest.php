<?php

namespace App\Http\Requests\Api\Schedule;

use App\Http\Requests\BaseRequest;
use App\Rules\UniqueScheduleRule;
use App\Rules\ValidDeviceIdRule;
use App\Rules\ValidGroupIdRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateRequest extends BaseRequest
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
            'id' => 'required|exists:schedules,id',
            'name' => 'required',
            'geo_location' => 'required|boolean',
            'geo_location_id' => 'required_if:geo_location,1|exists:geo_locations,id',
            'public' => 'required|boolean',
            'slots' => 'required|array|size:6',
            'slots.*.start_time' => 'required|date_format:H:i:s',
            'slots.*.value_a' => 'required|min:0|max:100',
            'slots.*.value_b' => 'required|min:0|max:100',
            'slots.*.value_c' => 'required|min:0|max:100',
            'slots.*.type' => 'required|in:' . TYPE_STEP . ',' . TYPE_GRADUAL,
            'enabled' => 'required|boolean',
            'device_id' => ['nullable', 'integer', 'exists:devices,id', new ValidDeviceIdRule()],
            'group_id' => ['nullable','integer','exists:groups,id', new ValidGroupIdRule()],
            'moonlight_enabled' => 'required|boolean',
            'water_type' => 'nullable|in:'.WATER_FRESH.','.WATER_MARINE,
        ];
    }
}
