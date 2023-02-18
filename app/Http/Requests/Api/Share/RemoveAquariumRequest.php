<?php

namespace App\Http\Requests\Api\Share;

use App\Http\Requests\BaseRequest;
use App\Rules\ShareEmailRule;
use Illuminate\Support\Facades\Auth;

class RemoveAquariumRequest extends BaseRequest
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
            'aquarium_id' => 'required|exists:user_aquaria,aquarium_id',
            'user_id' => 'required|exists:user_aquaria,user_id',
            'email' => ['nullable', new ShareEmailRule()],
        ];
    }
}
