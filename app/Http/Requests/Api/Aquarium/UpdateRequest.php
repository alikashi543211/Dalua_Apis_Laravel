<?php

namespace App\Http\Requests\Api\Aquarium;

use App\Http\Requests\BaseRequest;
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
            'id' => 'required|exists:aquaria,id,user_id,' . Auth::id(),
            'name' => 'required|' . Rule::unique('aquaria', 'name')->ignore(request('id'), 'id')->where('user_id', Auth::id()),
            'temperature' => 'nullable',
            'ph' => 'nullable',
            'salinity' => 'nullable',
            'alkalinity' => 'nullable',
            'magnesium' => 'nullable',
            'nitrate' => 'nullable',
            'phosphate' => 'nullable'
        ];
    }
}
