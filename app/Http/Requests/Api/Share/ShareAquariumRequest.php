<?php

namespace App\Http\Requests\Api\Share;

use App\Http\Requests\BaseRequest;
use App\Rules\ShareEmailRule;
use Illuminate\Support\Facades\Auth;

class ShareAquariumRequest extends BaseRequest
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
            'aquarium_id' => 'required|exists:aquaria,id,user_id,' . Auth::id(),
            'user_id' => 'nullable|exists:users,id,role_id,' . USER_APP . '|not_auth:users,id',
            'email' => ['nullable', new ShareEmailRule()],
        ];
    }
}
