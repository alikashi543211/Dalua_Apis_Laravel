<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Schedule;
use App\Models\User;
use App\Traits\Admin\DashboardTrait;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private $user, $device, $schedule;
    use DashboardTrait;
    public function __construct()
    {
        $this->user = new User();
        $this->device = new Device();
        $this->schedule = new Schedule();
    }
    public function dashboard()
    {
        $users_count = $this->user->newQuery()->where('role_id', USER_APP)->count();
        $devices_count = $this->device->newQuery()->count();
        $public_schedules_count = $this->schedule->newQuery()->where('user_id', '!=', 1)->wherePublic(1)->count();
        $dalua_presets_count = $this->schedule->newQuery()->where('user_id', 1)->count();
        $schedules = $this->schedule->newQuery()->where('user_id', '!=', 1)->wherePublic(1)->with(['geolocation'])->orderBy('id', 'DESC')->limit(5)->get();
        $UserGraph = $this->getUserGraph();
        $countryUsers = $this->getSocialTraffic();
        return view("admin.dashboard", get_defined_vars());
    }

    private function getPeriods()
    {

    }


}
