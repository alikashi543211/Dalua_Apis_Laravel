<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommandLog extends Model
{
    use HasFactory;

    /**
     * Get the response
     *
     * @param  string  $value
     * @return string
     */
    public function getResponseAttribute($value)
    {
        return json_decode($value);
    }

    /**
     * Set the payload
     *
     * @param  string  $value
     * @return void
     */
    public function setPayloadAttribute($value)
    {
        return $this->attributes['payload'] = json_encode($value);
    }

    /**
     * Get the payload
     *
     * @param  string  $value
     * @return string
     */
    public function getPayloadAttribute($value)
    {
        return json_decode($value);
    }

    /**
     * Get the user that owns the CommandLog
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the device that owns the CommandLog
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id', 'id');
    }

    /**
     * Get the group that owns the CommandLog
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
    }
}
