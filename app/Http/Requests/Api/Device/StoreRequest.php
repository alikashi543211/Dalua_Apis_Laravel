<?php

namespace App\Http\Requests\Api\Device;

use App\Http\Requests\BaseRequest;
use App\Rules\ValidAquariumRule;
use App\Rules\ValidGroupIdRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreRequest extends BaseRequest
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
            'name' => 'required|min:3|max:100|' . Rule::unique('devices', 'name')->where('user_id', Auth::id()),
            'product_id' => 'nullable|exists:products,id',
            'aquarium_id' => ['nullable', 'integer', 'exists:aquaria,id', new ValidAquariumRule()],
            'group_id' => ['nullable','integer','exists:groups,id', new ValidGroupIdRule()],
            'mac_address' => 'required|' . Rule::unique('devices')->where('user_id', Auth::id()),
            'timezone' => 'required|timezone',
            'water_type' => 'nullable|in:'.WATER_FRESH.','.WATER_MARINE,
        ];
    }
}
