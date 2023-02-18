<?php

namespace App\Http\Requests\Api\Group;

use App\Http\Requests\BaseRequest;
use App\Rules\ValidAquariumRule;
use App\Rules\ValidDeviceIdRule;
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
            'name' => 'required|max:200|min:3|' . Rule::unique('groups', 'name')->where('user_id', Auth::id()),
            'devices' => 'nullable|array',
            'devices.*' => ['required', 'integer', 'exists:devices,id', new ValidDeviceIdRule()],
            'aquarium_id' => ['required', 'integer', 'exists:aquaria,id', new ValidAquariumRule()],
            'timezone' => 'required|timezone'
        ];
    }
}
