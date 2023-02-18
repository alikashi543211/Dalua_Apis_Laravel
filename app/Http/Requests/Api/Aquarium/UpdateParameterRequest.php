<?php

namespace App\Http\Requests\Api\Aquarium;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Auth;

class UpdateParameterRequest extends BaseRequest
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
            'id' => 'required|exists:aquarium_parameters',
            'ph' => 'required|numeric',
            'temperature' => 'required|numeric',
            'salinity' => 'required|numeric',
            'calcium' => 'required|numeric',
            'alkalinity' => 'required|numeric',
            'magnesium' => 'required|numeric',
            'nitrate' => 'required|numeric',
            'phosphate' => 'required|numeric',
        ];
    }
}
