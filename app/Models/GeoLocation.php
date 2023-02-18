<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeoLocation extends Model
{
    use HasFactory;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['lat', 'lng', 'created_at', 'updated_at'];

    /**
     * Get the Weather Data
     *
     * @param  string  $value
     * @return string
     */
    public function getWeatherDataAttribute($value)
    {
        return json_decode($value);
    }
}
