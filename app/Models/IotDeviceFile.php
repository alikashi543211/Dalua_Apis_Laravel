<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class IotDeviceFile extends Model
{
    use HasFactory;
    protected $fillable = ["name", "location", "version", "product_id"];

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

    /**
     * Get the product that owns the IotDeviceFile
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
