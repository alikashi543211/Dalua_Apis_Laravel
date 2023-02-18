<?php

namespace App\Console\Commands;

use App\Drivers\AwsCall;
use App\Models\GeoLocation;
use App\Models\Schedule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Ixudra\Curl\Facades\Curl;

class UpdateGeolocationsWeather extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weather:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update locations weather data';

    private $geolocation, $geolocationData;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->geolocation = new GeoLocation();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $geolocations = $this->getGeolocation();
        foreach ($geolocations as $key => $location) {
            if ($data = $this->getLocationData($location->lat, $location->lng, $location->name)) {
                if (!empty($location->weather_data->current->weather) && !empty($data->current->weather)) {
                    if ($location->weather_data->current->weather[0]->id != $data->current->weather[0]->id) {
                        $this->updateDeviceWeathers($location);
                    }
                }
                $location->weather_data = json_encode($data);
                if ($location->save()) {
                    $this->info($location->name . " => data updated");
                } else  $this->info($location->name . " => Error updating data");
            }
        }
        return Command::SUCCESS;
    }

    private function getLocationData($lat, $lng, $name)
    {
        $data = Curl::to("https://api.openweathermap.org/data/2.5/onecall?lat=$lat&lon=$lng&exclude=minutely,daily,hourly,alerts&appid=" . env('OPEN_MAPS_API'))
            ->asJsonResponse()
            ->returnResponseObject()
            ->get();
        if ($data->status == SUCCESS_200) {
            Log::info('Weather Updated => ' . $name);
            return $data->content;
        } else {
            Log::info('Weather Not found => ' . $name);
            return false;
        }
    }

    private function getGeolocation()
    {
        return $this->geolocation->newQuery()->get();
    }

    private function updateDeviceWeathers($location)
    {
        $schedules = Schedule::where('geo_location_id', $location->id)->whereGeoLocation(true)->whereEnabled(true)->where(function ($q) {
            $q->whereHas('device', function ($que) {
                $que->whereDoesntHave('group');
            })->orWhereHas('group');
        })->get();
        Log::info("Schedules Count =>" . count($schedules));
        foreach ($schedules as $schedule) {
            $aws = new AwsCall();
            $relation = null;
            if ($schedule->group_id) {
                $relation = $schedule->group;
            } else {
                $relation = $schedule->device;
            }
            Log::info("Topic =>" . $relation->topic);
            Log::info("Schedule =>" . $schedule->name);
            $aws->sendScheduleToAws($schedule, $relation);
        }
    }
}
