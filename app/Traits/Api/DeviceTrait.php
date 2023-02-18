<?php

namespace App\Traits\Api;

use App\Models\IotDeviceConfiguration;
use Illuminate\Support\Str;

trait DeviceTrait
{
    private function saveDeviceHistory($device, $message = NULL)
    {
        $is_history = $this->deviceHistory
            ->whereMacAddress($device->mac_address)
            ->whereName($device->name)
            ->whereTopic($device->topic)
            ->whereUserId(auth()->user()->id)->exists();
        if (!$is_history) {
            $deviceHistory = $this->deviceHistory->newInstance();
            $deviceHistory->user_id = auth()->user()->id;
            $deviceHistory->name = $device->name;
            $deviceHistory->mac_address = $device->mac_address;
            $deviceHistory->topic = $device->topic;
            $deviceHistory->device_id = $device->id;
            $deviceHistory->type = LOG_TYPE_TOPIC;
            $deviceHistory->message = $message;
            if (!$deviceHistory->save()) {
                return false;
            }
        }

        return true;
    }

    private function AttachDeviceConfiguration($inputs, $water_type = null)
    {
        if ($water_type) {
            $configurations = IotDeviceConfiguration::where('iot_device_id', $water_type == WATER_FRESH ? 2 : 1)->get();
        } else {
            $configurations = IotDeviceConfiguration::where('iot_device_id', isset($inputs['water_type']) && $inputs['water_type'] == WATER_FRESH ? 2 : 1)->get();
        }

        $data = [];
        foreach ($configurations as $key => $config) {
            $data['channel_' . Str::lower($config->channel)][] = [
                'light' => $config->light,
                'rgba' => $config->rgba,
                'hex' => $config->hex,
                'coral_name' => $config->color_name,
            ];
        }

        return $data;
    }

    private function isGroupTypeSameAsDeviceType($inputs)
    {
        $group = $this->group->newQuery()->whereId($inputs['group_id'])->first();
        $device = $this->device->newQuery()->whereId($inputs['id'])->first();
        if ($group->water_type) {
            if ($group->water_type != $device->water_type) {
                return [false, REQUIRE_SAME_WATER_TYPE];
            }
        } else {
            if (!$device->water_type) {
                return [false, REQUIRE_WATER_TYPE];
            }
        }
        if (!$group->water_type) {
            $group->water_type = $device->water_type;
            $group->configuration = $this->AttachDeviceConfiguration($inputs, $device->water_type);
            $group->save();
        }
        return [true];
    }
    private function isGroupTypeSameAsDeviceTypeUpdateStatus($inputs, $groupId)
    {
        $group = $this->group->newQuery()->whereId($groupId)->first();
        $device = $this->device->newQuery()->whereId($inputs['id'])->first();
        if ($group->water_type) {
            if ($group->water_type != $device->water_type) {
                return [false, REQUIRE_SAME_WATER_TYPE];
            }
        } else {
            if (!$device->water_type) {
                return [false, REQUIRE_WATER_TYPE];
            }
        }
        if (!$group->water_type) {
            $group->water_type = $device->water_type;
            $group->configuration = $this->AttachDeviceConfiguration($inputs, $device->water_type);
            $group->save();
        }
        return [true];
    }

    private function setGroupType($groupId)
    {
        $deviceCount = $this->device->newQuery()->whereGroupId($groupId)->count();
        if ($deviceCount == 0) {
            $group = $this->group->newQuery()->whereId($groupId)->first();
            $group->water_type = null;
            if (!$group->save()) {
                return false;
            }
        }
        return true;
    }

    private function getMyDeviceDetail($id)
    {
        return $this->device->newQuery()->whereId($id)->with(['product'])->first();
    }

    private function isWaterTypeUpdatedForAllSchedules($device)
    {
        $schedules = $this->schedule->newQuery()->whereDeviceId($device->id)->get();
        if(count($schedules) > 0)
        {
            foreach($schedules as $key => $sch)
            {
                $sch->water_type = $device->water_type;
                if(!$sch->save())
                {
                    return false;
                }
            }
        }
        return true;
    }

}
