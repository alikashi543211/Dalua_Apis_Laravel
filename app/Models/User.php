<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Facades\JWTAuth;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'username',
        'role_id',
        'phone_no',
        'login_type',
        'tank_size',
        'social_user_id',
        'social_token',
        'country',
        'country_code',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'role_id',
        'jwt_sign',
        'email_verified_at',
        'verification_code',
        'social_user_id',
        'social_token',

    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    /*
     * generate Token
     * */

    public function getImageAttribute($value)
    {
        if(env('AWS_ENV') && !is_null($value) && Storage::disk('s3')->exists($value)){
            $expired_at = now()->addHour();
            return Storage::disk('s3')->temporaryUrl($value, $expired_at);
        }
        return url('assets/img/avatar.png');
    }

    public function generateJWTToken($ttl = 0)
    {
        if ($ttl) {
            JWTAuth::factory()->setTTL($ttl);
        }

        return JWTAuth::fromUser($this);
    }


    public function getFullNameAttribute()
    {
        return $this->first_name.' '.$this->middle_name.' '.$this->last_name;
    }

    /* Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * this function is to return user's info
     *
     * @param      $user
     * @param bool $withToken
     *
     * @return array
     * @internal param $token
     */
    public function returnUser()
    {
        //return user info
        $toReturn = [
            'user' => $this
        ];

        return $toReturn;
    }

    /**
     * Get all of the devices for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function devices(): HasMany
    {
        return $this->hasMany(Device::class, 'user_id', 'id');
    }

    /**
     * Get all of the groups for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function groups(): HasMany
    {
        return $this->hasMany(Group::class, 'user_id', 'id');
    }

    /**
     * The sharedDevices that belong to the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function sharedDevices(): BelongsToMany
    {
        return $this->belongsToMany(Device::class, 'user_devices', 'user_id', 'device_id');
    }

    public function sharedAquaria(): BelongsToMany
    {
        return $this->belongsToMany(Aquarium::class, 'user_aquaria', 'user_id', 'aquarium_id');
    }

    public function userAquariums(): HasMany
    {
        return $this->hasMany(UserAquarium::class, 'user_id', 'id');
    }

    public function aquariumList(): BelongsToMany
    {
        return $this->belongsToMany(Aquarium::class, 'user_aquaria', 'user_id', 'aquarium_id');
    }

    /**
     * The sharedGroups that belong to the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function sharedGroups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'user_groups', 'user_id', 'group_id');
    }

    /**
     * Get all of the products for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'user_id', 'id');
    }

    /**
     * Get all of the aquaria for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function aquaria(): HasMany
    {
        return $this->hasMany(Aquarium::class, 'user_id', 'id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'user_id', 'id');
    }
}
