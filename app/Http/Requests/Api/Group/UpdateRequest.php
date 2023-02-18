<?php

namespace App\Http\Requests\Api\Group;

use App\Http\Requests\BaseRequest;
use App\Rules\ValidAquariumRule;
use App\Rules\ValidDeviceIdRule;
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
            'id' => 'required|exists:groups,id,user_id,' . Auth::id(),
            'name' => 'required|max:200|min:3' . Rule::unique('groups', 'name')->where('id', request('id'))->where('user_id', Auth::id()),
            'aquarium_id' => ['required', 'exists:aquaria,id', new ValidAquariumRule()],
            'timezone' => 'required|timezone',
            'devices' => 'nullable|array',
            'devices.*' => ['required', 'integer', 'exists:devices,id', new ValidDeviceIdRule()]
        ];
    }
}
