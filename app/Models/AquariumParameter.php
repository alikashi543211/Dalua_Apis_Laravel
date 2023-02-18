<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AquariumParameter extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'temperature',
        'ph',
        'salinity',
        'calcium',
        'alkalinity',
        'magnesium',
        'nitrate',
        'phosphate',
        'aquarium_id'
    ];

}
