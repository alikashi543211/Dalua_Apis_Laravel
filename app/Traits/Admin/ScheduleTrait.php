<?php

namespace App\Traits\Admin;

use Illuminate\Support\Facades\DB;

trait ScheduleTrait
{
    private function scheduleFilterListing($inputs, $query)
    {
        if(isset($inputs['enabled']))
        {
            $query->where('enabled', $inputs['enabled']);
        }
        if(isset($inputs['geo_location_id']))
        {
            if($inputs['geo_location_id'] == '0')
            {
                $query->where('geo_location_id', NULL);
            }else{
                $query->where('geo_location_id', '!=', NULL);
            }

        }
        if(isset($inputs['mode']))
        {
            $query->where('mode', $inputs['mode']);
        }
        if(isset($inputs['water_type']))
        {
            $query->where('water_type', $inputs['water_type']);
        }
        if(isset($inputs['public']))
        {
            $query->where('public', $inputs['public']);
        }

        return $query;
    }

    private function getRedirectRoute($schedule)
    {
        $route = 'admin.schedules.listing';
        if($schedule->user_id == 1)
        {
            $route = 'admin.schedules.listingDalua';
        }elseif($schedule->public && $schedule->approval == PENDING_APPROVAL)
        {
            $route = 'admin.schedules.requests';

        }elseif($schedule->public && $schedule->approval == ACCEPTED_APPROVAL)
        {
            $route = 'admin.schedules.public_requests';
        }
        return route($route).'?id='.$schedule->id;
    }


}


