<?php

namespace App\Http\Requests\Api\Device;

use App\Http\Requests\BaseRequest;
use App\Rules\ValidAquariumRule;
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
            'id' => ['required', 'integer', 'exists:devices,id', new ValidDeviceIdRule()],
            'name' => 'required|min:3|max:100|' . Rule::unique('devices', 'name')->ignore(request('id'), 'id')->where('user_id', Auth::id()),
            'product_id' => 'nullable|exists:products,id',
            'product' => 'nullable|exists:products,name',
            'aquarium_id' => ['nullable', 'integer', 'exists:aquaria,id', new ValidAquariumRule()],
            'group_id' => ['nullable','integer','exists:groups,id', new ValidGroupIdRule()],
            'topic' => 'nullable|min:3|max:100|unique:devices,topic',
            'timezone' => 'required|timezone',
            'water_type' => 'nullable|in:'.WATER_FRESH.','.WATER_MARINE,
        ];
    }
}
