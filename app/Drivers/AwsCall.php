<?php

namespace App\Drivers;

use App\Jobs\TopicSubscribeJob;
use App\Models\CommandLog;
use App\Models\WeatherConfiguration;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpMqtt\Client\Facades\MQTT;

use function PHPUnit\Framework\returnCallback;

class AwsCall
{
    private $commandLog, $timestamp;

    public function __construct()
    {
        $this->commandLog = new CommandLog();
    }

    public function sendScheduleToAws($schedule, $relation, $forceTopic = NULL)
    {
        $topic = $forceTopic ? $forceTopic : $relation->topic;
        // dispatch(new TopicSubscribeJob($topic));
        // sleep(1);
        $message = $this->formatScheduleData($schedule, $relation);
        if (class_basename($relation) == 'Device') {
            $group = false;
        } else $group = true;

        $this->logCommand($schedule, $message, $group);
        $this->publishTopic($topic, $message);
        // dd("IOk");

        return true;
    }

    private function formatScheduleData($schedule, $relation)
    {
        $timezone = $relation->timezone ? $relation->timezone : 'Asia/Karachi';
        $timestamp = $this->timestamp = strtotime(Carbon::now()->setTimezone($timezone)->format('Y-m-d H:i:s'));
        $message = [
            'commandID' => 4,
            'deviceID' => '1',
            'macAddress' => $relation->mac_address ? $relation->mac_address : '1234',
            'isGroup' => $schedule->group_id ? true : false,
            'timestamp' => (string) $timestamp,
            'currentTime' => [(int) date('s', $timestamp), (int)  date('i', $timestamp), (int)  date('H', $timestamp), (int) date('N', $timestamp), (int) date('j', $timestamp), (int) date('n', $timestamp)],
            'timeSet' => $this->formatTimeSet($schedule),
            'colorSet' => $this->formatColorData($schedule)
        ];
        return $message;
    }

    private function formatTimeSet($schedule)
    {
        $timeSet = [];
        foreach ($schedule->sorted_slots as $key => $slot) {
            $startTime = strtotime($slot->start_time);
            $typeValue = $this->getSlotTypeValue($slot->type, $key);
            $timeSet[] = (int) date('H', $startTime);
            $timeSet[] = (int) date('i', $startTime);
            $timeSet[] = (int) date('s', $startTime);
            $timeSet[] = $typeValue;
        }
        return $timeSet;
    }

    private function getSlotTypeValue($type, $index)
    {
        $typeValue = 0;
        switch ($index) {
            case 0:
                $typeValue = ($type == TYPE_STEP ? 0 : 1);
                break;

            case 1:
                $typeValue = ($type == TYPE_STEP ? 16 : 17);
                break;

            case 2:
                $typeValue = ($type == TYPE_STEP ? 32 : 33);
                break;

            case 3:
                $typeValue = ($type == TYPE_STEP ? 48 : 49);
                break;

            case 4:
                $typeValue = ($type == TYPE_STEP ? 64 : 65);
                break;

            case 5:
                $typeValue = ($type == TYPE_STEP ? 80 : 81);
                break;
        }

        return $typeValue;
    }

    private function formatColorData($schedule)
    {
        $colorSet = [];
        $slots = json_decode($schedule->getRawOriginal('slots'), true);
        $slots[5]['moon_light'] = true;
        $slots = array_values(collect($slots)->sortBy('start_time')->toArray());
        $slots = json_decode(json_encode($slots));
        $location = $schedule->geo_location;
        $geoLocation = $schedule->geolocation;
        foreach ($slots as $key => $slot) {

            if ($schedule->mode == SCHEDULE_ADVANCED && $schedule->moonlight_enabled && !empty($slot->moon_light)) {
                $colorValues = $this->getMoonLightValues();
                $colorSet[] = (string) round($colorValues['value_c'], 0);
                $colorSet[] = (string) round($colorValues['value_b'], 0);
                $colorSet[] = (string) round($colorValues['value_a'], 0);
            } else if ($location) {
                // Dont need to switch already changed (C = A, A = C) in below method
                $colorValues = $this->getWeatherValues($slot, $geoLocation);
                $colorSet[] = (string) round($colorValues['value_c'], 0);
                $colorSet[] = (string) round($colorValues['value_b'], 0);
                $colorSet[] = (string) round($colorValues['value_a'], 0);
            } else {
                $colorSet[] = (string) $slot->value_c;
                $colorSet[] = (string) $slot->value_b;
                $colorSet[] = (string) $slot->value_a;
            }


            $colorSet[] = "0";
        }
        return $colorSet;
    }

    public function getWeatherValues($slot, $geoLocation)
    {
        $valueC = (int) $slot->value_c;
        $valueB = (int) $slot->value_b;
        $valueA = (int) $slot->value_a;
        $colorValues = [
            'value_c' => $valueC,
            'value_b' => $valueB,
            'value_a' => $valueA
        ];
        $id = $geoLocation->weather_data->current->weather[0]->id;
        // Log::info('Weather Id => ' . $id);
        if ($id >= 200 && $id <= 299) {
            $config = WeatherConfiguration::where('name', WEATHER_THUNDER_STORM)->where('water_type', $geoLocation->water_type)->first();
            $colorValues['value_c'] = (string) $valueC * $config->value_c;
            $colorValues['value_b'] = (string) $valueB * $config->value_b;
            $colorValues['value_a'] = (string) $valueA * $config->value_a;
        } else if ($id >= 500 && $id <= 599) {
            $config = WeatherConfiguration::where('name', WEATHER_RAIN)->where('water_type', $geoLocation->water_type)->first();
            $colorValues['value_c'] = (string) $valueC * $config->value_c;
            $colorValues['value_b'] = (string) $valueB * $config->value_b;
            $colorValues['value_a'] = (string) $valueA * $config->value_a;
        } else if ($id == 800) {
            $config = WeatherConfiguration::where('name', WEATHER_SUNNY)->where('water_type', $geoLocation->water_type)->first();
            $colorValues['value_c'] = (string) $valueC * $config->value_c;
            $colorValues['value_b'] = (string) $valueB * $config->value_b;
            $colorValues['value_a'] = (string) $valueA * $config->value_a;
        } else if ($id == 801 || $id == 802) {
            $config = WeatherConfiguration::where('name', WEATHER_PARTLY_CLOUDY)->where('water_type', $geoLocation->water_type)->first();
            $colorValues['value_c'] = (string) $valueC * $config->value_c;
            $colorValues['value_b'] = (string) $valueB * $config->value_b;
            $colorValues['value_a'] = (string) $valueA * $config->value_a;
        } else if ($id == 803 || $id == 804) {
            $config = WeatherConfiguration::where('name', WEATHER_CLOUDY)->where('water_type', $geoLocation->water_type)->first();
            $colorValues['value_c'] = (string) $valueC * $config->value_c;
            $colorValues['value_b'] = (string) $valueB * $config->value_b;
            $colorValues['value_a'] = (string) $valueA * $config->value_a;
        }
        return $colorValues;
    }

    public function subscribe($topic)
    {
        try {
            $mqtt = MQTT::connection();
            $mqtt->subscribe($topic, function ($topic, $msg) use ($mqtt) {
                updateCommandLog($msg, $topic, $mqtt);
            });
            $mqtt->loop(true, true);
            MQTT::disconnect();
        } catch (QueryException $e) {
            DB::rollBack();
            return Log::error('Error => ' . $e->getMessage());
        } catch (Exception $e) {
            DB::rollBack();
            return Log::error('Error => ' . $e->getMessage());
        }
    }



    public function updateLogCommand($response, $commandId, $topic)
    {
        if ($command = $this->commandLog->newQuery()->first()) {
            $command->reponse = $response;
            $command->status = true;
            $command->save();
            // Log::info("Log Id => " . $command->id . ", Command Updated");
        }
        // Log::info("Command Id Not Found");
    }

    private function logCommand($schedule, $payload, $group = false)
    {
        $payload['scheduleName'] = $schedule->name;
        if($group){
            foreach($schedule->group->devices AS $dev)
            {
                $log = $this->commandLog->newInstance();
                $log->user_id = Auth::id();
                $log->command_id = 4;
                $log->mac_address = $dev->mac_address;
                $log->timestamp = $this->timestamp;
                $log->payload = $payload;
                $log->topic = $schedule->group->topic . '/ack';
                $log->group_id = $schedule->group_id;
                $log->save();
            }
        }else{
            $log = $this->commandLog->newInstance();
            $log->user_id = Auth::id();
            $log->command_id = 4;
            $log->mac_address = $schedule->device->mac_address;
            $log->timestamp = $this->timestamp;
            $log->payload = $payload;
            $log->topic = $schedule->device->topic . '/ack';
            $log->device_id = $schedule->device_id;
            $log->save();
        }
    }

    private function getMoonLightValues()
    {
        $solaris = new Solaris();
        $age = (int) $solaris->get('age');

        $colorValues = [
            'value_a' => "0",
            'value_b' => "0",
            'value_c' => "0"
        ];

        if ($age >= 3 && $age <= 7) {
            $colorValues['value_c'] = "1";
        } else if ($age >= 8 && $age <= 11) {
            $colorValues['value_b'] = "1";
            $colorValues['value_c'] = "1";
        } else if ($age >= 12 && $age <= 16) {
            $colorValues['value_a'] = "1";
            $colorValues['value_b'] = "1";
            $colorValues['value_c'] = "1";
        } else if ($age >= 17 && $age <= 22) {
            $colorValues['value_b'] = "1";
            $colorValues['value_c'] = "1";
        } else if ($age >= 23 && $age <= 27) {
            $colorValues['value_c'] = "1";
        }
        return $colorValues;
    }

    public function publishTopic($topic, $message)
    {
        MQTT::publish($topic, json_encode($message));
        MQTT::disconnect();
    }
}
