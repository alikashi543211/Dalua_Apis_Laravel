<?php

namespace App\Console\Commands;

use App\Drivers\AwsCall;
use App\Models\GeoLocation;
use App\Models\Schedule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Ixudra\Curl\Facades\Curl;

class UpdateScheduleMoonLight extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moonlight:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update moonlight data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $schedules = Schedule::where('moonlight_enabled', true)->whereEnabled(true)->where(function ($q) {
            $q->whereHas('device', function ($que) {
                $que->whereDoesntHave('group');
            })->orWhereHas('group');
        })->get();
        foreach ($schedules as $key => $schedule) {
            $aws = new AwsCall();
            $relation = null;
            if ($schedule->group_id) {
                $relation = $schedule->group;
            } else {
                $relation = $schedule->device;
            }
            $aws->sendScheduleToAws($schedule, $relation);
        }
        return Command::SUCCESS;
    }
}
