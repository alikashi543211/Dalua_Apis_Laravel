<?php

namespace App\Http\Requests\Api\Aquarium;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Auth;

class SharedUsersRequest extends BaseRequest
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
            'aquarium_id' => 'required|exists:aquaria,id'
        ];
    }
}
