<?php

namespace App\Http\Requests\Api\Category;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
            'name' => 'required|min:3|max:200|' . Rule::unique('categories', 'name')->where('user_id', Auth::id())->ignore(request('id')),
            'category_id' => 'nullable|exists:categories,id'
        ];
    }
}
