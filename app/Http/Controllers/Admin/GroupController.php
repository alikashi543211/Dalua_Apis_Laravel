<?php

namespace App\Http\Controllers\Admin;

use App\Drivers\AwsCall;
use App\Http\Controllers\Controller;
use App\Models\CommandLog;
use App\Models\Device;
use App\Models\Group;
use App\Models\Schedule;
use Illuminate\Http\Request;


class GroupController extends Controller
{
    private $device, $group, $schedule;
    public function __construct()
    {
        $this->device = new Device();
        $this->group = new Group();
        $this->commandLog = new CommandLog();
        $this->schedule = new Schedule();
    }

    public function listing(Request $request)
    {
        $inputs = $request->all();
        $query = $this->group->newQuery()->orderBy('created_at', 'DESC')->with(['user', 'devices', 'aquarium', 'users', 'schedule']);
        if (!empty($inputs['search'])) {
            $query->where(function ($q) use ($inputs) {
                searchTable($q, $inputs['search'], ['name', 'topic', 'uid', 'timezone']);
                searchTable($q, $inputs['search'], ['name'], 'aquarium');
                searchTable($q, $inputs['search'], ['first_name', 'last_name'], 'user');
            });
        }
        if(!empty($inputs['water_type']))
        {
            $query->whereWaterType($inputs['water_type']);
        }
        $groups = $query->paginate(PAGINATE);
        return view('admin.groups.listing', compact('groups'));
    }

    public function detail($id)
    {
        $group = $this->group->newQuery()->whereId($id)
            ->with(['user', 'devices', 'aquarium', 'users', 'schedule'])
            ->first();
        $schedules = $this->schedule->newQuery()->whereGroupId($id)->get();
        $devices = $this->device->newQuery()->whereGroupId($id)->get();
        $commands = $this->commandLog->newQuery()->whereCommandId(4)->whereGroupId($id)->orderBy('id', 'DESC')->get();
        return view('admin.groups.detail', compact('group', 'schedules', 'devices', 'commands'));
    }


    public function instantControl(Request $request)
    {
        $group = $this->group->newQuery()->whereId($request->id)
            ->first();
        $awsCall = new AwsCall();

        $message = [
            "commandID" => 3, "deviceID" => isset($group->devices[0]) ? $group->devices[0]->uniqid : '', "macAddress" => "", "isGroup" => true, "timestamp" => now(),
            "a_value" => $request->c_value,
            "b_value" => $request->b_value,
            "c_value" => $request->a_value,
        ];
        $awsCall->publishTopic($group->topic, $message);

        return response()->json(['success' => true]);
    }
}
