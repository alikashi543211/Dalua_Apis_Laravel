<?php

namespace App\Jobs;

use App\Drivers\AwsCall;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TopicSubscribeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $awsCall;
    protected $topic;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($topic)
    {
        $this->awsCall = new AwsCall();
        $this->topic = $topic;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->awsCall->subscribe($this->topic . '/ack');
    }
}
