<?php

namespace App\Http\Requests\Api\Group;

use App\Http\Requests\BaseRequest;
use App\Rules\ValidGroupIdRule;
use Illuminate\Support\Facades\Auth;

class DetailRequest extends BaseRequest
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
            'id' => ['required','integer','exists:groups,id', new ValidGroupIdRule()],
        ];
    }
}
