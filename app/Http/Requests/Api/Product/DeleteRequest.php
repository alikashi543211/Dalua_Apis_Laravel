<?php

namespace App\Http\Requests\Api\Product;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Auth;

class DeleteRequest extends BaseRequest
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
            'id' => 'required|exists:products,id,user_id,' . Auth::id()
        ];
    }
}
