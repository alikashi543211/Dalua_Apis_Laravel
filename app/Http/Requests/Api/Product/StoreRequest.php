<?php

namespace App\Http\Requests\Api\Product;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Auth;

class StoreRequest extends BaseRequest
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
            'name' => 'required|min:3|max:200',
            'model' => 'required|min:3|max:200',
            'category_id' => 'required|exists:categories,id,user_id,' . Auth::id(),
            'sub_category_id' => 'required|exists:categories,id,category_id,' . request('category_id') . ',user_id,' . Auth::id(),
            'image' => 'nullable',
            'specification' => 'nullable|min:3|max:500'
        ];
    }
}
