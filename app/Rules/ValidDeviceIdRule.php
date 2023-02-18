<?php

namespace App\Rules;

use App\Models\Device;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ValidDeviceIdRule implements Rule
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
        return Device::where(function($q) use($value){
            $q->whereUserId(Auth::id())->whereId($value);
        })->orWhere(function($q) use($value){
            $q->whereId($value)->whereHas('aquarium', function($q){
                $q->whereHas('userAquariums', function($q){
                    $q->where('user_aquaria.user_id', Auth::id())->where('user_aquaria.status', SHARED_AQUARIUM_STATUS_ACCEPTED);
                });
            });
        })->orWhere(function($q) use($value){
            $q->whereId($value)->whereHas('users', function($q){
                $q->where('users.id', Auth::id());
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
