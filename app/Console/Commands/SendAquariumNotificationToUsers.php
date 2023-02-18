<?php

namespace App\Console\Commands;

use App\Jobs\PushNotificationJob;
use App\Models\Aquarium;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendAquariumNotificationToUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aquarium:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notification to users about testing or changing their aquariums';
    private $aquarium;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->aquarium = new Aquarium();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $cleanUsers = [];
        $testUsers = [];
        $aquariums = $this->aquarium->newQuery()->get();
        foreach ($aquariums as $key => $aquarium) {
            if ($this->checkAquariumCleanDate($aquarium)) {
                $cleanUsers[] = $aquarium->user;
                $aquarium->last_clean = Carbon::now();
            }
            if ($this->checkAquariumTestDate($aquarium)) {
                $testUsers[] = $aquarium->user;
                $aquarium->last_test = Carbon::now();
            }
            $aquarium->save();
        }
        $this->sendNotificationToUsers($cleanUsers, __('notification.cleanAquarium.type'));
        $this->sendNotificationToUsers($testUsers, __('notification.testAquarium.type'));
        return Command::SUCCESS;
    }

    private function sendNotificationToUsers($users, $type)
    {
        $data = [
            'users' => $users,
            'type' => $type
        ];

        dispatch(new PushNotificationJob($data));
    }

    private function checkAquariumCleanDate($aquarium)
    {
        if ($aquarium->clean_frequency == FREQUENCY_WEEK) {
            if (Carbon::parse($aquarium->last_clean)->format('Y-m-d') >= Carbon::now()->subWeek()->format('Y-m-d')) {
                return true;
            } else return false;
        } else if ($aquarium->clean_frequency == FREQUENCY_WEEK) {
            if (Carbon::parse($aquarium->last_clean)->format('Y-m-d') >= Carbon::now()->subWeeks(2)->format('Y-m-d')) {
                return true;
            } else return false;
        } else if ($aquarium->clean_frequency == FREQUENCY_WEEK) {
            if (Carbon::parse($aquarium->last_clean)->format('Y-m-d') >= Carbon::now()->subMonth()->format('Y-m-d')) {
                return true;
            } else return false;
        } else return false;
    }

    private function checkAquariumTestDate($aquarium)
    {
        if ($aquarium->test_frequency == FREQUENCY_WEEK) {
            if (Carbon::parse($aquarium->last_test)->format('Y-m-d') >= Carbon::now()->subWeek()->format('Y-m-d')) {
                return true;
            } else return false;
        } else if ($aquarium->test_frequency == FREQUENCY_WEEK) {
            if (Carbon::parse($aquarium->last_test)->format('Y-m-d') >= Carbon::now()->subWeeks(2)->format('Y-m-d')) {
                return true;
            } else return false;
        } else if ($aquarium->test_frequency == FREQUENCY_WEEK) {
            if (Carbon::parse($aquarium->last_test)->format('Y-m-d') >= Carbon::now()->subMonth()->format('Y-m-d')) {
                return true;
            } else return false;
        } else return false;
    }
}
