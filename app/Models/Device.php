<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Str;

class Device extends Model
{
    use HasFactory;
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'version' => 'integer',
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['product_id', 'aquarium_id', 'group_id', 'name', 'mac_address', 'ip_address', 'wifi', 'device_name'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['user_id', 'aquarium_id', 'wifi_ssid', 'timezone'];

    /**
     * Get the Configuration
     *
     * @param  string  $value
     * @return string
     */
    public function getConfigurationAttribute($value)
    {
        $data = [];
        $configurations = IotDeviceConfiguration::where('iot_device_id', isset($this->water_type) && $this->water_type == WATER_FRESH ? 2 : 1)->get();
        foreach ($configurations as $key => $config) {
            $data['channel_' . Str::lower($config->channel)][] = [
                'light' => $config->light,
                'rgba' => $config->rgba,
                'hex' => $config->hex
            ];
        }

        return count($data) ? $data : null;

    }

    public function getStatusAttribute($value)
    {
        if(!is_null($this->topic) && !is_null($this->device_topic))
        {
            if($this->topic != $this->device_topic)
            {
                return 2;
            }
        }
        return $value;
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

    /**
     * Get the user that owns the Device
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the group that owns the Device
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
    }

    /**
     * Get the product that owns the Device
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * Get the aquarium that owns the Device
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function aquarium(): BelongsTo
    {
        return $this->belongsTo(Aquarium::class, 'aquarium_id', 'id');
    }

    /**
     * Get the settings associated with the Device
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function settings(): HasOne
    {
        return $this->hasOne(DeviceSetting::class, 'device_id', 'id');
    }

    /**
     * The users that belong to the Device
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_devices', 'device_id', 'user_id');
    }

    /**
     * Get the user associated with the Device
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function schedule(): HasOne
    {
        return $this->hasOne(Schedule::class, 'device_id', 'id')->where('enabled', true);
    }

    /**
     * Get the Topic
     *
     * @param  string  $value
     * @return string
     */
    public function getTopicAttribute($value)
    {
        if ($this->group_id) {
            return DB::table('groups')->where('id', $this->group_id)->first()->topic;
        } else return $value;
    }
}
