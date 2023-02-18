<?php

namespace App\Http\Requests\Api\Aquarium;

use App\Http\Requests\BaseRequest;
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
            'name' => 'required|' . Rule::unique('aquaria', 'name')->where('user_id', Auth::id()),
            'temperature' => 'nullable|numeric',
            'ph' => 'nullable|numeric',
            'salinity' => 'nullable|numeric',
            'alkalinity' => 'nullable|numeric',
            'magnesium' => 'nullable|numeric',
            'nitrate' => 'nullable|numeric',
            'phosphate' => 'nullable|numeric'
        ];
    }
}
