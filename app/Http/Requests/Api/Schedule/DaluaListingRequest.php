<?php

namespace App\Http\Requests\Api\Schedule;

use App\Http\Requests\BaseRequest;

class DaluaListingRequest extends BaseRequest
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
            'water_type' => 'nullable|in:'.WATER_FRESH.','.WATER_MARINE
        ];
    }
}
