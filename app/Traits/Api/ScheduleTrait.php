<?php

namespace App\Traits\Api;

use Illuminate\Support\Str;

trait ScheduleTrait
{
    private function isWaterTypeUpdated($schedule)
    {
        if($schedule->device_id)
        {
            $device = $this->device->newQuery()->whereId($schedule->device_id)->first();
            if($device)
            {
                $schedule->water_type = $device->water_type;
                if(!$schedule->save())
                {
                    return false;
                }
            }
        }

        if($schedule->group_id)
        {
            $group = $this->group->newQuery()->whereId($schedule->group_id)->first();
            if($group)
            {
                $schedule->water_type = $group->water_type;
                if(!$schedule->save())
                {
                    return false;
                }
            }
        }

        return true;
    }
}
