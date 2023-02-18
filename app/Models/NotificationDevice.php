<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid', 'token', 'type', 'user_id'
    ];

}
