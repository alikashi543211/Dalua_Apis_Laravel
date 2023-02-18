<?php

namespace App\Traits\Admin;

use App\Models\Device;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Support\Facades\DB;

trait DashboardTrait
{
    private function getUserGraph()
    {
        $dates = [];
        $start    = (new DateTime(date('Y-m-d', strtotime("-1 Year"))))->modify('first day of this month');
        $end      = (new DateTime(date('Y-m-d', strtotime('-1 Month'))))->modify('first day of next month');
        $interval = DateInterval::createFromDateString('1 Month');
        $period = new DatePeriod($start, $interval, $end);
        foreach ($period as $key => $dt) {
            $dates[$key]['date_name'] = $dt->format("M, Y");
            $dates[$key]['date_value'] = $dt->format("Y-m");
        }
        // dd($dates);
        $data = [];

        foreach($dates AS $key => $date)
        {
            $data['months'][] = $date['date_name'];
            $data['users'][] = $this->user->newQuery()->where('created_at', 'LIKE', $date['date_value']."%")->count();
            $data['devices'][] = Device::where('created_at', 'LIKE', $date['date_value']."%")->count();
        }
        return $data;
    }

    private function getSocialTraffic()
    {
        $totalUsers = $this->user->newQuery()->where('country', '!=', NULL)->where('id', '!=', 1)->count();
        $countryUsers = DB::table('users')
            ->where('country', '!=', NULL)
            ->where('id', '!=', 1)
            ->select('country', DB::raw('COUNT(*) AS total'))
            ->orderBy('total', 'DESC')
            ->limit('5')
            ->groupBy('country')->get();
        $progress_colors = ['success', 'primary', 'info', 'warning', 'danger'];
        foreach($countryUsers as $key => $item)
        {
            $obtainedUsers = $this->user->newQuery()
                ->where('country', '!=', NULL)
                ->where('id', '!=', 1)
                ->whereCountry($item->country)->count();
            $item->percentage = round(($obtainedUsers/$totalUsers)*100);
            $item->progress_color = $progress_colors[$key];
        }
        return $countryUsers;
    }


}
