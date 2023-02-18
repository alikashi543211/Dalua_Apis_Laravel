<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GeoLocation\ListingRequest;
use App\Models\Device;
use App\Models\GeoLocation;
use Illuminate\Http\Request;

class GeolocationController extends Controller
{
    private $location, $device;

    public function __construct()
    {
        $this->location = new GeoLocation();
        $this->device = new Device();
    }

    public function listing(ListingRequest $request)
    {
        $inputs = $request->all();
        $query = $this->location->newQuery();
        if(!empty($inputs['device_id']))
        {
            $water_type = $this->device->newQuery()->whereId($inputs['device_id'])->value('water_type');
            if(!$water_type)
            {
                return $this->error(__('device.water_type'), ERROR_400);
            }
            $query->whereWaterType($water_type);
        }
        $locations = $query->get();
        return $this->successWithData('Geolocatons Fetched', $locations);
    }
}
