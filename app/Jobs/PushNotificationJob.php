<?php

namespace App\Jobs;

use App\Traits\NotificationTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NotificationTrait;
    protected $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $type = $this->data['type'];
        switch ($type) {
            case __('notification.cleanAquarium.type'):
                $this->cleanAquariumNotication();
                break;

            case __('notification.testAquarium.type'):
                $this->testAquariumNotication();
                break;
            case __('notification.sharedAquarium.type'):
                $this->sharedAquariumNotication();
                break;
            case __('notification.acceptAquarium.type'):
                $this->acceptAquariumNotication();
                break;

            default:
                # code...
                break;
        }
    }

    private function cleanAquariumNotication()
    {
        $data = __('notification.cleanAquarium');
        $this->sendNotificationToMultipleUsers($this->data['users'], $data);
    }

    private function testAquariumNotication()
    {
        $data = __('notification.testAquarium');
        $this->sendNotificationToMultipleUsers($this->data['users'], $data);
    }

    private function sharedAquariumNotication()
    {
        $data = __('notification.sharedAquarium', [ 'username' => $this->data['username'], 'aquarium' => $this->data['aquarium_name'] ]);
        $this->sendNotificationToSingleUser($this->data['user'], $data);
    }
    private function acceptAquariumNotication()
    {
        $data = __('notification.acceptAquarium', [ 'username' => $this->data['username'], 'aquarium' => $this->data['aquarium_name'] ]);
        $this->sendNotificationToSingleUser($this->data['user'], $data);
    }
}
