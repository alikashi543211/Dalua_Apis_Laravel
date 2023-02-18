<?php

namespace App\Http\Requests\Api\Group;

use App\Http\Requests\BaseRequest;
use App\Rules\ValidAquariumRule;
use Illuminate\Support\Facades\Auth;

class ListingRequest extends BaseRequest
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
            'aquarium_id' => ['required', 'integer', 'exists:aquaria,id', new ValidAquariumRule()],
        ];
    }
}
