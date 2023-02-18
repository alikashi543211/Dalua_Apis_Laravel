<?php

namespace App\Console\Commands;

use App\Drivers\AwsCall;
use Illuminate\Console\Command;

class IotDeviceReponseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'device:response {topic}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscribe to device and get reponse messages';
    private $awsCall;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->awsCall = new AwsCall();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->awsCall->subscribe($this->argument('topic'));
        $this->info('Ok');
        return Command::SUCCESS;
    }
}
