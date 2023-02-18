<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class IotDeviceFileV4 extends Model
{
    use HasFactory;

    /**
     * Get the name
     *
     * @param  string  $value
     * @return string
     */
    public function getLocationAttribute($value)
    {
        return $value ? (env('AWS_ENV') ? $value : public_path($value)) : '';
    }
}
