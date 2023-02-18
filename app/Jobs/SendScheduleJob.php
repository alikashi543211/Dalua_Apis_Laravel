<?php

namespace App\Jobs;

use App\Drivers\AwsCall;
use App\Models\WeatherConfiguration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendScheduleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $schedule, $aws;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($schedule)
    {
        $this->schedule = $schedule;
        $this->aws = new AwsCall();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $topic = $this->schedule->device->topic;
        $message = $this->formatScheduleData();
        $output = $this->aws->publishTopic($topic, $message);
        return true;
    }

    private function formatScheduleData()
    {
        $schedule = $this->schedule;
        $message = [
            'commandID' => 4,
            'deviceID' => '1',
            'macAddress' => $schedule->device->mac_address,
            'isGroup' => true,
            'timestamp' => strtotime('+5 hours'),
            'currentTime' => [(int) date('s', strtotime('+5 hours')), (int)  date('i', strtotime('+5 hours')), (int)  date('H', strtotime('+5 hours')), (int) date('N', strtotime('+5 hours')), (int) date('j', strtotime('+5 hours')), (int) date('n', strtotime('+5 hours'))],
            'timeSet' => $this->formatTimeSet(),
            'colorSet' => $this->formatColorData()
        ];
        return $message;
    }

    private function formatTimeSet()
    {
        $timeSet = [];
        foreach ($this->schedule->slots as $key => $slot) {
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
                $typeValue = ($type == TYPE_GRADUAL ? 0 : 1);
                break;

            case 1:
                $typeValue = ($type == TYPE_GRADUAL ? 16 : 17);
                break;

            case 2:
                $typeValue = ($type == TYPE_GRADUAL ? 32 : 33);
                break;

            case 3:
                $typeValue = ($type == TYPE_GRADUAL ? 48 : 49);
                break;

            case 4:
                $typeValue = ($type == TYPE_GRADUAL ? 64 : 65);
                break;

            case 5:
                $typeValue = ($type == TYPE_GRADUAL ? 80 : 81);
                break;
        }

        return $typeValue;
    }

    private function formatColorData()
    {
        $colorSet = [];
        $slots = $this->schedule->slots;
        $location = $this->schedule->geo_location;
        $geoLocation = $this->schedule->geolocation;
        foreach ($slots as $key => $slot) {
            if ($location) {
                $colorValues = $this->getWeatherValues($slot, $geoLocation);
                $colorSet[] = (int) round($colorValues['value_a'], 0);
                $colorSet[] = (int) round($colorValues['value_b'], 0);
                $colorSet[] = (int) round($colorValues['value_c'], 0);
            } else {
                $colorSet[] = $slot->value_a;
                $colorSet[] = $slot->value_b;
                $colorSet[] = $slot->value_c;
            }
            $colorSet[] = 0;
        }
        return $colorSet;
    }

    public function getWeatherValues($slot, $geoLocation)
    {
        $valueA = (int) $slot->value_a;
        $valueB = (int) $slot->value_b;
        $valueC = (int) $slot->value_c;
        $colorValues = [
            'value_a' => $valueA,
            'value_b' => $valueB,
            'value_c' => $valueC
        ];
        // $id = $geoLocation->weather_data->current->weather[0]->id;
        $id = 250;
        if ($id >= 200 && $id <= 299) {
            $config = WeatherConfiguration::where('name', WEATHER_THUNDER_STORM)->first();
            $colorValues['value_a'] = $valueA * $config->value_a;
            $colorValues['value_b'] = $valueB * $config->value_b;
            $colorValues['value_c'] = $valueC * $config->value_c;
        } else if ($id >= 500 && $id <= 599) {
            $config = WeatherConfiguration::where('name', WEATHER_RAIN)->first();
            $colorValues['value_a'] = $valueA * $config->value_a;
            $colorValues['value_b'] = $valueB * $config->value_b;
            $colorValues['value_c'] = $valueC * $config->value_c;
        } else if ($id == 800) {
            $config = WeatherConfiguration::where('name', WEATHER_SUNNY)->first();
            $colorValues['value_a'] = $valueA * $config->value_a;
            $colorValues['value_b'] = $valueB * $config->value_b;
            $colorValues['value_c'] = $valueC * $config->value_c;
        } else if ($id == 801 || $id == 802) {
            $config = WeatherConfiguration::where('name', WEATHER_PARTLY_CLOUDY)->first();
            $colorValues['value_a'] = $valueA * $config->value_a;
            $colorValues['value_b'] = $valueB * $config->value_b;
            $colorValues['value_c'] = $valueC * $config->value_c;
        } else if ($id == 803 || $id == 804) {
            $config = WeatherConfiguration::where('name', WEATHER_CLOUDY)->first();
            $colorValues['value_a'] = $valueA * $config->value_a;
            $colorValues['value_b'] = $valueB * $config->value_b;
            $colorValues['value_c'] = $valueC * $config->value_c;
        }
        return $colorValues;
    }
}
