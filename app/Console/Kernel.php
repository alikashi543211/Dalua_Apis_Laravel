<?php

namespace App\Console;

use App\Console\Commands\PendingScheduleRequests;
use App\Console\Commands\UpdateGeolocationsWeather;
use App\Console\Commands\UpdateScheduleMoonLight;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        UpdateGeolocationsWeather::class,
        UpdateScheduleMoonLight::class,
        PendingScheduleRequests::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(UpdateGeolocationsWeather::class)->hourly();
        $schedule->command(UpdateScheduleMoonLight::class)->dailyAt('17:00');
        $schedule->command(PendingScheduleRequests::class)->hourly();
        $schedule->command('telescope:prune --hours=48')->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {

        config(['logging.default' => 'command']);
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
