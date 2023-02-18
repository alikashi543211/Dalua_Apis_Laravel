<?php

namespace App\Console\Commands;

use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PendingScheduleRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pending-schedule-requests:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pending Schedule Requests Will Be Deleted After Two Weeks.';

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
        Schedule::whereApproval(PENDING_APPROVAL)->whereNotNull('approval')->whereNotNull('requested_at')
            ->where('requested_at', '<', Carbon::now()->subDays(14))->delete();

        return Command::SUCCESS;
    }
}
