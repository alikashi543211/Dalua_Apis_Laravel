<?php

namespace App\Http\Requests\Api\Share;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Auth;

class ShareGroupRequest extends BaseRequest
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
            'group_id' => 'required|exists:groups,id,user_id,' . Auth::id(),
            'users' => 'required|array|min:1|max:5',
            'users.*' => 'required|exists:users,id,role_id,' . USER_APP . '|not_auth:users,id'
        ];
    }
}
