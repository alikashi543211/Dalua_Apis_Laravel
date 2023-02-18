<?php

namespace App\Rules;

use App\Models\Schedule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UniqueScheduleRule implements Rule
{
    private $id;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $this->id = $id;
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
        $scheduleId = $this->id;
        return Schedule::where(function($q) use($value, $scheduleId){
            $q->where('name', '=', $value);
            $q->where('id', '!=', $scheduleId);
            $q->whereUserId(Auth::id());
        })->orWhere(function($q) use($value, $scheduleId){
            $q->where('name', '=', $value);
            $q->where('id', '!=', $scheduleId);
            $q->whereHas('device', function($q) use($value){
                $q->whereHas('aquarium', function($q){
                    $q->whereHas('users', function($q){
                        $q->where('users.id', Auth::id());
                    });
                });
            });
        })->doesntExist();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ':attribute already been taken.';
    }
}
