<?php

namespace App\Http\Requests\Api\Aquarium;

use App\Http\Requests\BaseRequest;

class ChangeStatusRequest extends BaseRequest
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
            'id' => 'required|integer|exists:user_aquaria',
            'status' => 'required|in:'.SHARED_AQUARIUM_STATUS_ACCEPTED.','.SHARED_AQUARIUM_STATUS_REJECTED,
        ];
    }
}
