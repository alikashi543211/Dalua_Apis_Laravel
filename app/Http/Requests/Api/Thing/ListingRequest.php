<?php

namespace App\Http\Requests\Api\Thing;

use App\Http\Requests\BaseRequest;

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
            'attributeName' => 'required_with:attributeValue',
            'attributeValue' => 'required_with:attributeName',
            'maxResults' => 'required|integer|min:10'
        ];
    }
}
