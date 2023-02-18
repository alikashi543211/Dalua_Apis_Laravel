<?php

namespace App\Rules;

use App\Models\Aquarium;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ValidAquariumRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return Aquarium::where(function($q) use($value){
            $q->whereUserId(Auth::id())->whereId($value);
        })->orWhere(function($q) use($value){
            $q->whereHas('userAquariums', function($q) use($value){
                $q->where('user_aquaria.aquarium_id', $value)->where('user_aquaria.user_id', Auth::id())->where('user_aquaria.status', SHARED_AQUARIUM_STATUS_ACCEPTED);
            });
        })->exists();
    }


    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The selected :attribute is invalid.';
    }
}
