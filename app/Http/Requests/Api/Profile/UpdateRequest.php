<?php

namespace App\Http\Requests\Api\Profile;

use App\Http\Requests\BaseRequest;

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
            'first_name' => 'sometimes|required|string|min:3|max:200',
            'tank_size' => 'sometimes|required',
            'country' => 'sometimes|required',
            'image' => 'sometimes|required|file|image|max:2048',
        ];
    }
}
