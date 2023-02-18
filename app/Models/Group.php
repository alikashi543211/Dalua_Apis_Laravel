<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Str;
class Group extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'aquarium_id'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['user_id'];

    public function getConfigurationAttribute($value)
    {
        if(!empty($value) && $value != '[]'){
            return json_decode($value);
        }else{
            $data = [];
            $configurations = IotDeviceConfiguration::where('iot_device_id', isset($this->water_type) && $this->water_type == WATER_FRESH ? 2 : 1)->get();
            foreach ($configurations as $key => $config) {
                $data['channel_' . Str::lower($config->channel)][] = [
                    'light' => $config->light,
                    'rgba' => $config->rgba,
                    'hex' => $config->hex
                ];
            }

            return $data;
        }

    }

    /**
     * Set the Configuration
     *
     * @param  string  $value
     * @return void
     */
    public function setConfigurationAttribute($value)
    {
        return $this->attributes['configuration'] = json_encode($value);
    }

    public function getWaterTypeAttribute($value)
    {
        $count = DB::table('devices')->where('group_id', $this->id)->count();
        if($count == 0)
        {
            DB::table('groups')->where('id', $this->id)->update(['water_type' => NULL]);
            return NULL;
        }
        return $value;
    }

    /**
     * Get the user that owns the Group
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get all of the devices for the Group
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function devices(): HasMany
    {
        return $this->hasMany(Device::class, 'group_id', 'id');
    }

    /**
     * Get the aquarium that owns the Group
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function aquarium(): BelongsTo
    {
        return $this->belongsTo(Aquarium::class, 'aquarium_id', 'id');
    }

    /**
     * The users that belong to the Group
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_groups', 'group_id', 'user_id');
    }


    /**
     * Get the user associated with the Device
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function schedule(): HasOne
    {
        return $this->hasOne(Schedule::class, 'group_id', 'id')->where('enabled', true);
    }
}
