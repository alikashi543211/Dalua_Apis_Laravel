<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Api\ScheduleController as AppScheduleController;
use App\Http\Controllers\Controller;
use App\Models\GeoLocation;
use App\Models\Schedule;
use Carbon\Carbon;
use Doctrine\DBAL\Query\QueryException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Drivers\AwsCall;
use App\Traits\Admin\ScheduleTrait;

class ScheduleController extends Controller
{
    private $schedule, $geolocation;
    use ScheduleTrait;
    public function __construct()
    {
        $this->schedule = new Schedule();
        $this->geolocation = new GeoLocation();
    }

    public function listing(Request $request)
    {

        $inputs = $request->all();
        $query = $this->schedule->newQuery()->where('user_id', '!=', 1)->with(['geolocation']);
        if (!empty($inputs['search'])) {
            $query->where(function ($q) use ($inputs) {
                searchTable($q, $inputs['search'], ['name']);
                searchTable($q, $inputs['search'], ['name', 'topic'], 'group');
                searchTable($q, $inputs['search'], ['name', 'topic'], 'device');
                searchTable($q, $inputs['search'], ['name'], 'geolocation');
                searchTable($q, $inputs['search'], ['first_name', 'last_name'], 'user');
            });
        }
        $this->scheduleFilterListing($inputs, $query);
        $schedules = $query->orderBy('created_at', 'DESC')->paginate(PAGINATE);
        return view('admin.schedules.listing', compact('schedules'));
    }

    public function scheduleRequests(Request $request)
    {
        $inputs = $request->all();
        $query = $this->schedule->newQuery()->where('user_id', '!=', 1)->with(['geolocation'])->whereApproval(PENDING_APPROVAL);
        if (!empty($inputs['search'])) {
            $query->where(function ($q) use ($inputs) {
                searchTable($q, $inputs['search'], ['name']);
                searchTable($q, $inputs['search'], ['name', 'topic'], 'group');
                searchTable($q, $inputs['search'], ['name', 'topic'], 'device');
                searchTable($q, $inputs['search'], ['name'], 'geolocation');
                searchTable($q, $inputs['search'], ['first_name', 'last_name'], 'user');
            });
        }
        $this->scheduleFilterListing($inputs, $query);
        $schedules = $query->orderBy('created_at', 'DESC')->paginate(PAGINATE);
        return view('admin.schedules.requests', compact('schedules'));
    }

    public function schedulePublicRequests(Request $request)
    {
        $inputs = $request->all();
        $query = $this->schedule->newQuery()->where('user_id', '!=', 1)->wherePublic(1)->whereApproval(ACCEPTED_APPROVAL)->with(['geolocation']);
        if (!empty($inputs['search'])) {
            $query->where(function ($q) use ($inputs) {
                searchTable($q, $inputs['search'], ['name']);
                searchTable($q, $inputs['search'], ['name', 'topic'], 'group');
                searchTable($q, $inputs['search'], ['name', 'topic'], 'device');
                searchTable($q, $inputs['search'], ['name'], 'geolocation');
                searchTable($q, $inputs['search'], ['first_name', 'last_name'], 'user');
            });
        }
        $this->scheduleFilterListing($inputs, $query);
        $schedules = $query->orderBy('created_at', 'DESC')->paginate(PAGINATE);
        return view('admin.schedules.public_requests', compact('schedules'));
    }

    public function listingDalua(Request $request)
    {
        $inputs = $request->all();
        $query = $this->schedule->newQuery()->where('user_id', 1)->with(['geolocation'])->orderBy('created_at', 'DESC');
        if (!empty($inputs['search'])) {
            $query->where(function ($q) use ($inputs) {
                searchTable($q, $inputs['search'], ['name']);
                searchTable($q, $inputs['search'], ['name', 'topic'], 'group');
                searchTable($q, $inputs['search'], ['name', 'topic'], 'device');
                searchTable($q, $inputs['search'], ['name'], 'geolocation');
                searchTable($q, $inputs['search'], ['first_name', 'last_name'], 'user');
            });
        }
        $this->scheduleFilterListing($inputs, $query);
        $schedules = $query->paginate(PAGINATE);
        return view('admin.schedules.listing-dalua', compact('schedules'));
    }

    public function add()
    {
        $locations = $this->geolocation->newQuery()->get();
        return view('admin.schedules.add', compact('locations'));
    }

    public function addEasy()
    {
        $locations = $this->geolocation->newQuery()->get();
        return view('admin.schedules.add-easy', compact('locations'));
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $schedule = $this->schedule->newInstance();
            $schedule->name = 'Name';
            $schedule->fill($inputs);
            $schedule->user_id = Auth::id();
            $schedule->public = true;
            if ($schedule->save()) {
                $slots = [];
                for ($i = 0; $i < 6; $i++) {
                    $data = [
                        'start_time' => Carbon::parse($inputs['start_time'][$i])->format('H:i:s'),
                        'value_a' => $inputs['value_a'][$i],
                        'value_b' => $inputs['value_b'][$i],
                        'value_c' => $inputs['value_c'][$i],
                        'type' => $inputs['type_' . $i]
                    ];
                    $slots[] = $data;
                }
                $schedule->slots = $slots;
                if ($schedule->save()) {
                    $route = $this->getRedirectRoute($schedule);
                    DB::commit();
                    return redirect()->to($route)->with('success', 'New schedule added successfully.');
                }
            }
            DB::rollBack();
            return redirect()->back()->with('error', 'Error while adding new schedule. Please try again.');
        } catch (QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error while adding new schedule. Please try again.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error while adding new schedule. Please try again.');
        }
    }

    public function updateApproval(Request $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $schedule = $this->schedule->newQuery()->whereId($inputs['id'])->first();
            $schedule->approval = $inputs['approval'];
            if ($schedule->save()) {
                DB::commit();
                return redirect()->back()->with('success', 'New schedule added successfully.');
            }
            DB::rollBack();
            return redirect()->back()->with('error', 'Error while adding new schedule. Please try again.');
        } catch (QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error while adding new schedule. Please try again.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error while adding new schedule. Please try again.');
        }
    }

    public function storeEasy(Request $request)
    {
        try {
            DB::beginTransaction();

            $apiScheduleController = new AppScheduleController();
            $inputs = $request->all();
            $schedule = $this->schedule->newInstance();
            $schedule->name = $inputs['name'];
            $inputs['ramp_time'] = '0' . $inputs['ramp_time'] . ':00';
            $inputs = $apiScheduleController->formatEasySlots($inputs);
            $schedule->fill($inputs);
            $schedule->user_id = Auth::id();
            $schedule->public = true;
            $schedule->mode = SCHEDULE_EASY;
            $schedule->slots = $apiScheduleController->convertEasyToAdvanceSlots($inputs);
            if ($schedule->save()) {
                $route = $this->getRedirectRoute($schedule);
                DB::commit();
                return redirect()->to($route)->with('success', 'New schedule added successfully.');
            }
            DB::rollBack();
            return redirect()->back()->with('error', 'Error while adding new schedule. Please try again.');
        } catch (QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error while adding new schedule. Please try again.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error while adding new schedule. Please try again.');
        }
    }

    public function edit($id)
    {
        $schedule = $this->schedule->newQuery()->where('id', $id)->with(['geolocation'])->first();
        $locations = $this->geolocation->newQuery()->get();
        return view('admin.schedules.edit', compact('schedule', 'locations'));
    }
    public function editEasy($id)
    {
        $schedule = $this->schedule->newQuery()->where('id', $id)->with(['geolocation'])->first();
        // dd($schedule->easy_slots->sunrise);
        $locations = $this->geolocation->newQuery()->get();
        return view('admin.schedules.edit-easy', compact('schedule', 'locations'));
    }

    public function getScheduleData($id)
    {
        $schedule = $this->schedule->newQuery()->where('id', $id)->first();
        $slots = $schedule->slots;

        $slots = collect($slots);

        $data = [];
        if($schedule->water_type == WATER_FRESH)
        {
            $data[] = $this->setGraphData($slots, "Channel A", "value_a", "rgba(241,82,11,0.2)", "rgba(241,82,11,1)");
            $data[] = $this->setGraphData($slots, "Channel B", "value_b", "rgba(101,255,0,0.2)", "rgba(101,255,0,1)");
            $data[] = $this->setGraphData($slots, "Channel C", "value_c", "rgba(254,248,47,0.2)", "rgba(254,248,47,1)");
        }else{
            $data[] = $this->setGraphData($slots, "Channel A", "value_a", "rgba(136,55,249,0.2)", "rgba(136,55,249,1)");
            $data[] = $this->setGraphData($slots, "Channel B", "value_b", "rgba(67,169,255,0.2)", "rgba(67,169,255,1)");
            $data[] = $this->setGraphData($slots, "Channel C", "value_c", "rgba(227,220,220,0.2)", "rgba(227,220,220,1)");
        }


        return response()->json([
            'data' => $data,
            'hours' => $this->getMinMax($slots, $schedule)
        ]);
    }
    private function getMinMax($slots, $sch)
    {

        $hours0 = (int) date('H', strtotime("1990-01-01 " . $slots[0]->start_time));
        $hours1 = (int) date('H', strtotime("1990-01-01 " . $slots[1]->start_time));
        $hours2 = (int) date('H', strtotime("1990-01-01 " . $slots[2]->start_time));
        $hours3 = (int) date('H', strtotime("1990-01-01 " . $slots[3]->start_time));
        $hours4 = (int) date('H', strtotime("1990-01-01 " . $slots[4]->start_time));
        $hours5 = (int) date('H', strtotime("1990-01-01 " . $slots[5]->start_time));

        $hours1 = $hours0 > $hours1 ? $hours1 + 24 : $hours1;
        $hours2 = $hours0 > $hours2 ? $hours2 + 24 : $hours2;
        $hours3 = $hours0 > $hours3 ? $hours3 + 24 : $hours3;
        $hours4 = $hours0 > $hours4 ? $hours4 + 24 : $hours4;
        $hours5 = $hours0 > $hours5 ? $hours5 + 24 : $hours5;

        $min = min([$hours0, $hours1, $hours2, $hours3, $hours4, $hours5]);
        $max = max([$hours0, $hours1, $hours2, $hours3, $hours4, $hours5]);
        $gap = 630/($max - $min);
        return ['min' => $min, 'max' => $max+2,
            'time0' => substr($slots[0]->start_time, 0, -3),
            'time1' => substr($slots[1]->start_time, 0, -3),
            'time2' => substr($slots[2]->start_time, 0, -3),
            'time3' => substr($slots[3]->start_time, 0, -3),
            'time4' => substr($slots[4]->start_time, 0, -3),
            'time5' => substr($slots[5]->start_time, 0, -3),
            'time1_gap' => (($hours1 - $hours0) * $gap) + 35,
            'time2_gap' => (($hours2 - $hours0) * $gap) + 35,
            'time3_gap' => (($hours3 - $hours0) * $gap) + 35,
            'time4_gap' => (($hours4 - $hours0) * $gap) + 35,
            'time5_gap' => (($hours5 - $hours0) * $gap) + 35,
            'moon' => $sch->moonlight_enabled
        ];
    }
    private function setGraphData($slots, $channelName, $channelAttribute, $bgColor, $brColor)
    {
        $data = array(
                "label" => $channelName,
                "backgroundColor" => $bgColor,
                "borderColor" => $brColor,
                "pointRadius" => 0,
                "hoverBackgroundColor" => $bgColor,
                "hoverBorderColor" => $brColor,
                "data" => array()
            );

        $counter = 0;
        $hours0 = (int) date('H', strtotime("1990-01-01 " . $slots[0]->start_time));
        $hours1 = (int) date('H', strtotime("1990-01-01 " . $slots[1]->start_time));
        $hours2 = (int) date('H', strtotime("1990-01-01 " . $slots[2]->start_time));
        $hours3 = (int) date('H', strtotime("1990-01-01 " . $slots[3]->start_time));
        $hours4 = (int) date('H', strtotime("1990-01-01 " . $slots[4]->start_time));
        $hours5 = (int) date('H', strtotime("1990-01-01 " . $slots[5]->start_time));

        $hours1 = $hours0 > $hours1 ? $hours1 + 24 : $hours1;
        $hours2 = $hours0 > $hours2 ? $hours2 + 24 : $hours2;
        $hours3 = $hours0 > $hours3 ? $hours3 + 24 : $hours3;
        $hours4 = $hours0 > $hours4 ? $hours4 + 24 : $hours4;
        $hours5 = $hours0 > $hours5 ? $hours5 + 24 : $hours5;

        if($slots[0]->type == TYPE_GRADUAL){
            $data["data"][$counter]['x'] = $hours0;
            $data["data"][$counter]['y'] = $slots[0]->{$channelAttribute};
        }else{
            $data["data"][$counter]['x'] = $hours0;
            $data["data"][$counter]['y'] = $slots[0]->{$channelAttribute};
            $counter++;
            $data["data"][$counter]['x'] = $hours1;
            $data["data"][$counter]['y'] = $slots[0]->{$channelAttribute};
        }

        $counter++;
        if($slots[1]->type == TYPE_GRADUAL){
            $data["data"][$counter]['x'] = $hours1;
            $data["data"][$counter]['y'] = $slots[1]->{$channelAttribute};
        }else{
            $data["data"][$counter]['x'] = $hours1;
            $data["data"][$counter]['y'] = $slots[1]->{$channelAttribute};
            $counter++;
            $data["data"][$counter]['x'] = $hours2;
            $data["data"][$counter]['y'] = $slots[1]->{$channelAttribute};
        }

        $counter++;
        if($slots[2]->type == TYPE_GRADUAL){
            $data["data"][$counter]['x'] = $hours2;
            $data["data"][$counter]['y'] = $slots[2]->{$channelAttribute};
        }else{
            $data["data"][$counter]['x'] = $hours2;
            $data["data"][$counter]['y'] = $slots[2]->{$channelAttribute};
            $counter++;
            $data["data"][$counter]['x'] = $hours3;
            $data["data"][$counter]['y'] = $slots[2]->{$channelAttribute};
        }

        $counter++;
        if($slots[3]->type == TYPE_GRADUAL){
            $data["data"][$counter]['x'] = $hours3;
            $data["data"][$counter]['y'] = $slots[3]->{$channelAttribute};
        }else{
            $data["data"][$counter]['x'] = $hours3;
            $data["data"][$counter]['y'] = $slots[3]->{$channelAttribute};
            $counter++;
            $data["data"][$counter]['x'] = $hours4;
            $data["data"][$counter]['y'] = $slots[3]->{$channelAttribute};
        }

        $counter++;
        if($slots[4]->type == TYPE_GRADUAL){
            $data["data"][$counter]['x'] = $hours4;
            $data["data"][$counter]['y'] = $slots[4]->{$channelAttribute};
        }else{
            $data["data"][$counter]['x'] = $hours4;
            $data["data"][$counter]['y'] = $slots[4]->{$channelAttribute};
            $counter++;
            $data["data"][$counter]['x'] = $hours5;
            $data["data"][$counter]['y'] = $slots[4]->{$channelAttribute};
        }

        $counter++;
        if($slots[5]->type == TYPE_GRADUAL){
            $data["data"][$counter]['x'] = $hours5;
            $data["data"][$counter]['y'] = $slots[5]->{$channelAttribute};
        }else{
            $time = (int) date('H', strtotime("1990-01-01 " . $slots[5]->start_time));
            $data["data"][$counter]['x'] = $hours5;
            $data["data"][$counter]['y'] = $slots[5]->{$channelAttribute};
        }

        return $data;
    }
    public function getEasyScheduleData($id)
    {
        $schedule = $this->schedule->newQuery()->where('id', $id)->first();
        $slot = $schedule->easy_slots;
        $data = [];
        if($schedule->water_type == WATER_FRESH)
        {
            $data[] = $this->easySetGraphData($slot, "Channel A", "value_a", "rgba(241,82,11,0.2)", "rgba(241,82,11,1)");
            $data[] = $this->easySetGraphData($slot, "Channel B", "value_b", "rgba(101,255,0,0.2)", "rgba(101,255,0,1)");
            $data[] = $this->easySetGraphData($slot, "Channel C", "value_c", "rgba(254,248,47,0.2)", "rgba(254,248,47,1)");
        }else{
            $data[] = $this->easySetGraphData($slot, "Channel A", "value_a", "rgba(136,55,249,0.2)", "rgba(136,55,249,1)");
            $data[] = $this->easySetGraphData($slot, "Channel B", "value_b", "rgba(67,169,255,0.2)", "rgba(67,169,255,1)");
            $data[] = $this->easySetGraphData($slot, "Channel C", "value_c", "rgba(227,220,220,0.2)", "rgba(227,220,220,1)");
        }

        return response()->json([
            'data' => $data,
            'hours' => $this->getEasyMinMax($slot)
        ]);
    }
    private function getEasyMinMax($slot)
    {
        $sunrise = (int) date('H', strtotime("1990-01-01 " . $slot->sunrise));
        $sunset = (int) date('H', strtotime("1990-01-01 " . $slot->sunset));
        $ramp = (int) date('H', strtotime("1990-01-01 " . $slot->ramp_time));

        $sunrise = $sunrise > $sunset ? $sunrise + 24 : $sunrise;

        $rampHour = (int) head(explode(':', $slot->ramp_time));
        $rampMinutes = (int) last(explode(':', $slot->ramp_time));

        $min = min([$sunrise, ($sunset+$ramp)]);
        $max = max([$sunrise, ($sunset+$ramp)]);
        $gap = 720/($max - $min);
        return ['min' => $min, 'max' => $max,
        'time0' => Carbon::parse($slot->sunrise)->format('H:i'),
        'time1' => Carbon::parse($slot->sunrise)->addHours($rampHour)->addMinutes($rampMinutes)->format('H:i'),
        'time2' => Carbon::parse($slot->sunset)->format('H:i'),
        'time3' => Carbon::parse($slot->sunset)->addHours($rampHour)->addMinutes($rampMinutes)->format('H:i'),
        'time1_gap' => ($ramp * $gap) + 35,
        'time2_gap' => (($sunset - $sunrise) * $gap) + 35,
        ];
    }
    private function easySetGraphData($slot, $channelName, $channelAttribute, $bgColor, $brColor)
    {
        $data = array(
            "label" => $channelName,
            "backgroundColor" => $bgColor,
            "borderColor" => $brColor,
            "pointRadius" => 0,
            "hoverBackgroundColor" => $bgColor,
            "hoverBorderColor" => $brColor,
            "data" => array()
        );

        $counter = 0;
        $sunrise = (int) date('H', strtotime("1990-01-01 " . $slot->sunrise));
        $sunset = (int) date('H', strtotime("1990-01-01 " . $slot->sunset));
        $ramp = (int) date('H', strtotime("1990-01-01 " . $slot->ramp_time));

        $sunrise = $sunrise > $sunset ? $sunrise + 24 : $sunrise;


        $data["data"][$counter]['x'] = $sunrise;
        $data["data"][$counter]['y'] = "0";
        $counter++;
        $data["data"][$counter]['x'] = $sunrise + $ramp;
        $data["data"][$counter]['y'] = $slot->{$channelAttribute};
        $counter++;
        $data["data"][$counter]['x'] = $sunset;
        $data["data"][$counter]['y'] = $slot->{$channelAttribute};
        $counter++;
        $data["data"][$counter]['x'] = $sunset + $ramp;
        $data["data"][$counter]['y'] = "0";

        return $data;
    }



    public function update(Request $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $schedule = $this->schedule->newQuery()->where('id', $inputs['id'])->first();
            $schedule->name = $inputs['name'];
            $schedule->fill($inputs);
            if ($schedule->save()) {
                $slots = [];
                for ($i = 0; $i < 6; $i++) {
                    $data = [
                        'start_time' => Carbon::parse($inputs['start_time'][$i])->format('H:i:s'),
                        'value_a' => $inputs['value_a'][$i],
                        'value_b' => $inputs['value_b'][$i],
                        'value_c' => $inputs['value_c'][$i],
                        'type' => $inputs['type_' . $i]
                    ];
                    $slots[] = $data;
                }
                $schedule->slots = $slots;
                if ($schedule->save()) {
                    $route = $this->getRedirectRoute($schedule);

                    DB::commit();
                    return redirect()->to($route)->with('success', 'New schedule added successfully.');
                }
            }
            DB::rollBack();
            return redirect()->back()->with('error', 'Error while adding new schedule. Please try again.');
        } catch (QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'errorMessage');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'errorMessage');
        }
    }

    public function updateEasy(Request $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $apiScheduleController = new AppScheduleController();
            $schedule = $this->schedule->newQuery()->where('id', $inputs['id'])->first();
            $schedule->name = $inputs['name'];
            $inputs['ramp_time'] = '0' . $inputs['ramp_time'] . ':00';
            $inputs = $apiScheduleController->formatEasySlots($inputs);
            $schedule->slots = $apiScheduleController->convertEasyToAdvanceSlots($inputs);
            $schedule->fill($inputs);
            if ($schedule->save()) {
                if ($schedule->save()) {
                    $route = $this->getRedirectRoute($schedule);
                    DB::commit();
                    return redirect()->to($route)->with('success', 'Schedule Updated Successfully.');
                }
            }
            DB::rollBack();
            return redirect()->back()->with('error', 'Error while adding new schedule. Please try again.');
        } catch (QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'errorMessage');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'errorMessage');
        }
    }

    public function delete($id)
    {
        try {
            DB::beginTransaction();
            $schedule = $this->schedule->newQuery()->where('id', $id)->first();
            if ($schedule->default) {
                return redirect()->back()->with('error', 'Cannot delete a default schedule.');
            }
            // if(count($schedule->devices)){
            //     return redirect()->back()->with('error','Schedule Assigned to Devices. Please remove devices before deleting schedule');
            // }else{
            if ($schedule->delete()) {
                DB::commit();
                return redirect()->back()->with('success', 'Schedule deleted successfully');
            } else {
                DB::rollBack();
                return redirect()->back()->with('error', 'Error while deleting schedule. Please try again later.');
            }
            // }
            DB::rollBack();
            return redirect()->back()->with('error', 'Error while deleting schedule. Please try again later.');
        } catch (QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function setDefaultSchedule($id)
    {
        try {
            DB::beginTransaction();
            $schedule = $this->schedule->newQuery()->where('user_id', 1)->find($id);
            if (!$schedule) {
                return redirect()->back()->with('error', 'Schedule not found');
            }
            $this->schedule->newQuery()->where('user_id', 1)->whereWaterType($schedule->water_type)->update(['default' => false]);
            $schedule->default = true;
            if ($schedule->save()) {
                DB::commit();
                return redirect()->back()->with('success', 'Schedule set to default successfully');
            }
            DB::rollBack();
            return redirect()->back()->with('error', 'Error while deleting schedule. Please try again later.');
        } catch (QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function scheduleUpload(Request $request, $id)
    {
        $schedule = $this->schedule->newQuery()->whereId($request->id)->first();

        $awsCall = new AwsCall();
        $schedule->enabled = 1;

        DB::beginTransaction();
        if ($schedule->save()) {
            $schedule = $schedule->fresh();
            if ($schedule->enabled) {
                $relation = NULL;
                if ($schedule->group_id) {
                    $relation = $schedule->group;
                    $this->disableOtherSchedules($schedule->id, $relation->id, true);
                } else {
                    $relation = $schedule->device;
                    $this->disableOtherSchedules($schedule->id, $relation->id, false);
                }
                if (!$awsCall->sendScheduleToAws($schedule, $relation)) {
                    DB::rollback();
                    return redirect()->back()->with('error', 'Something went wrong please try again');
                }
            }
            DB::commit();
        }
        return redirect()->back()->with('success', 'Schedule uploaded');
    }

    public function storeGraphImage(Request $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $schedule = $this->schedule->newQuery()->whereId($inputs['id'])->first();
            $image = $inputs['image'];
            $image = explode(";", $image)[1];
            $image = explode(",", $image)[1];
            $image = str_replace(" ", "+", $image);
            $image = base64_decode($image);
            $graph = $this->uploadFileGraph($image, $schedule, 'graph', false, 'Dalua-Presets', true);
            $schedule->graph = $graph;
            if(!$schedule->save())
            {
                DB::rollBack();
                return $this->error('Operation failed', ERROR_400);
            }
            DB::commit();
            return $this->successWithData('Graph saved successfully', $schedule);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }

    }

    private function disableOtherSchedules($id, $relationId, $group = false)
    {
        $query = $this->schedule->newQuery()->where('id', '!=', $id);
        if ($group) {
            $query->where('group_id', $relationId);
        } else $query->where('device_id', $relationId);
        $query->update(['enabled' => false]);
    }
}
