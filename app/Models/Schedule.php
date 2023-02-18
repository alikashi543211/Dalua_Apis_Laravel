<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Schedule extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'geo_location', 'geo_location_id', 'public', 'slots', 'easy_slots', 'enabled', 'mode', 'device_id', 'group_id', 'moonlight_enabled', 'water_type'];
    protected $appends = ['sorted_slots'];
    protected $with = ['location'];

    // protected $casts = ["public" => "boolean"];


    public function getGraphAttribute($value)
    {
        if(env('AWS_ENV') && !is_null($value) && Storage::disk('s3')->exists($value)){
            $expired_at = now()->addHour();
            return Storage::disk('s3')->temporaryUrl($value, $expired_at);
        }
        return null;
    }

    public function getWaterTypeAttribute($value)
    {

        if($this->device_id){
            $device = DB::table('devices')->where('id', $this->device_id)->first();
            return $device->water_type;
        }elseif($this->group_id){
            $group = DB::table('groups')->where('id', $this->group_id)->first();
            if($group->water_type)
            {
                return $group->water_type;
            }
        }
        return $value;
    }

    /**
     * Get the geolocation that owns the Schedule
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function geolocation(): BelongsTo
    {
        return $this->belongsTo(GeoLocation::class, 'geo_location_id', 'id');
    }

    /**
     * Get the user that owns the Schedule
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Set the slots
     *
     * @param  string  $value
     * @return void
     */
    public function setSlotsAttribute($value)
    {
        return $this->attributes['slots'] = json_encode($value);
    }

    /**
     * Get the slots
     *
     * @param  string  $value
     * @return string
     */
    public function getSlotsAttribute($value)
    {
        return json_decode($value);
    }
    /**
     * Get the slots
     *
     * @param  string  $value
     * @return string
     */
    public function getSortedSlotsAttribute()
    {
        return array_values(collect($this->slots)->sortBy('start_time')->toArray());
    }

    /**
     * Set the easy slots
     *
     * @param  string  $value
     * @return void
     */
    public function setEasySlotsAttribute($value)
    {
        $value['sunrise'] = Carbon::parse($value['sunrise'])->format("H:i:s");
        $value['sunset'] = Carbon::parse($value['sunset'])->format("H:i:s");
        return $this->attributes['easy_slots'] = json_encode($value);
    }

    /**
     * Get the easy slots
     *
     * @param  string  $value
     * @return string
     */
    public function getEasySlotsAttribute($value)
    {
        return json_decode($value);
    }

    /**
     * Get the group that owns the Schedule
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
    }

    /**
     * Get the device that owns the Schedule
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id', 'id');
    }

    /**
     * Get the location associated with the Schedule
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function location(): HasOne
    {
        return $this->hasOne(GeoLocation::class, 'id', 'geo_location_id');
    }
}
