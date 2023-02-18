<?php

namespace App\Http\Controllers\Api;

use App\Drivers\AwsCall;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Schedule\DaluaListingRequest;
use App\Http\Requests\Api\Schedule\DeleteRequest;
use App\Http\Requests\Api\Schedule\ListingRequest;
use App\Http\Requests\Api\Schedule\PublicListingRequest;
use App\Http\Requests\Api\Schedule\ResendRequest;
use App\Http\Requests\Api\Schedule\StoreGraphRequest;
use App\Http\Requests\Api\Schedule\StoreRequest;
use App\Http\Requests\Api\Schedule\UpdateEasyModeScheduleRequest;
use App\Http\Requests\Api\Schedule\UpdateNameRequest;
use App\Http\Requests\Api\Schedule\UpdateRequest;
use App\Models\Device;
use App\Models\Group;
use App\Models\Schedule;
use App\Models\WeatherConfiguration;
use App\Traits\Api\ScheduleTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Salman\Mqtt\MqttClass\Mqtt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ScheduleController extends Controller
{
    private $schedule, $awsCall, $device, $group, $pagination;
    use ScheduleTrait;

    public function __construct()
    {
        $this->device = new Device();
        $this->group = new Group();
        $this->schedule = new Schedule();
        $this->awsCall = new AwsCall();
        $this->pagination = request('page_size', PAGINATE);
    }

    public function listing(ListingRequest $request)
    {
        $inputs = $request->all();
        $query = $this->schedule->newQuery();

        if(!empty($inputs['aquarium_id']))
        {
            $query->where(function($q) use($inputs){
                $q->whereHas('device', function($q) use($inputs){
                    $q->whereHas('aquarium', function($q) use($inputs){
                        $q->whereId($inputs['aquarium_id']);
                    });
                });
            });
        }

        $schedules = $query->with('location');

        if(!empty(request('device_id'))){
            $schedules->whereDeviceId($inputs['device_id']);
        }

        if(!empty(request('group_id'))){
            $schedules->whereGroupId($inputs['group_id']);
        }

        return $this->successWithData(__('schedule.fetched'), $schedules->paginate(PAGINATE));
    }

    public function dalua(DaluaListingRequest $request)
    {
        $inputs = $request->all();
        $query = $this->schedule->newQuery()->where('user_id', 1);
        if(!empty($inputs['water_type']))
        {
            $query->whereWaterType($inputs['water_type']);
        }
        $schedules = $query->with('location')->paginate(PAGINATE);
        return $this->successWithData(__('schedule.listing'), $schedules);
    }

    public function public(PublicListingRequest $request)
    {
        $inputs = $request->all();
        $query = $this->schedule->newQuery()->whereNotIn('user_id', [1])->where('public', true)->whereApproval(ACCEPTED_APPROVAL);
        if(!empty($inputs['water_type']))
        {
            $query->whereWaterType($inputs['water_type']);
        }
        $schedules = $query->with('user', 'location')->paginate(PAGINATE);
        return $this->successWithData(__('schedule.listing'), $schedules);
    }

    private function queryUpdate($inputs, $schedule = null)
    {
        $query = $this->schedule->newQuery();
        if($schedule){
            $query->where('id', '!=', $schedule->id);
            if($schedule->group_id){
                $query->whereGroupId($schedule->group_id);
            }else{
                $query->whereDeviceId($schedule->device_id);
            }
        }else{
            if(!empty($inputs['group_id'])){
                $query->whereGroupId($inputs['group_id']);
            }else{
                $query->whereDeviceId($inputs['device_id']);
            }
        }

        return $query;
    }

    private function checkAndUpdateScheduleName($inputs, $schedule = null)
    {

        $query = $this->queryUpdate($inputs, $schedule);

        $query->whereName($inputs['name']);

        if($query->first()){
            $querySecond = $this->queryUpdate($inputs, $schedule);

            $querySecond->where('name', 'LIKE', $inputs['name'].' (%')->orderBy('id', 'DESC');

            if($sch = $querySecond->first()){
                $explode = explode(' (', $sch->name);
                $oldNumber = (int) str_replace(')', '', $explode[1]);
                $newNumber = $oldNumber + 1;
                return str_replace($oldNumber, $newNumber, $sch->name);
            }else{
                return $inputs['name'] . ' (1)';
            }
        }

        return $inputs['name'];
    }

    public function store(StoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();

            if (empty($inputs['group_id']) && empty($inputs['device_id'])) {
                return $this->error('the group id or device id is required', ERROR_400);
            }
            if (!empty($inputs['group_id']) && !empty($inputs['device_id'])) {
                return $this->error('Either group id or device id can be accepted', ERROR_400);
            }
            $inputs['name'] = $this->checkAndUpdateScheduleName($inputs);
            if ($inputs['mode'] == SCHEDULE_EASY) {
                unset($inputs['slots']);
                $inputs = $this->formatEasySlots($inputs);
                $inputs['slots'] = $this->convertEasyToAdvanceSlots($inputs);
            } else {
                unset($inputs['sunset'], $inputs['sunrise'], $inputs['ramp_time'], $inputs['value_a'], $inputs['value_b'], $inputs['value_c']);
            }

            $schedule = $this->schedule->newInstance();
            $schedule->fill($inputs);
            if(isset($inputs['public']) && $inputs['public']){
                $schedule->approval = PENDING_APPROVAL;
                $schedule->requested_at = Carbon::now();
            }
            if(!empty($inputs['device_id']))
            {
                $schedule->user_id = $this->device->whereId($inputs['device_id'])->value('user_id');
            }
            if(!empty($inputs['group_id']))
            {
                $schedule->user_id = $this->group->whereId($inputs['group_id'])->value('user_id');
            }
            $schedule->created_by = Auth::id();


            if ($schedule->save()) {
                $schedule = $schedule->fresh();
                if(!$this->isWaterTypeUpdated($schedule))
                {
                    DB::rollback();
                    return $this->error(__('schedule.add'), ERROR_400);
                }
                if ($schedule->enabled) {
                    $relation = NULL;
                    if ($schedule->group_id) {
                        $relation = $schedule->group;
                        $this->disableOtherSchedules($schedule->id, $relation->id, true);
                    } else {
                        $relation = $schedule->device;
                        $this->disableOtherSchedules($schedule->id, $relation->id, false);
                    }
                    if ($this->awsCall->sendScheduleToAws($schedule, $relation)) {
                        DB::commit();
                        return $this->successWithData(__('schedule.added'), $schedule);
                    } else {

                        DB::rollback();
                        return $this->error(__('schedule.add'), ERROR_400);
                    }
                }
                DB::commit();
                return $this->successWithData(__('schedule.added'), $schedule);
            }
            DB::rollback();
            return $this->error(__('schedule.add'), ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function updateName(UpdateNameRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $schedule = $this->schedule->newQuery()->whereId($inputs['id'])->first();
            $inputs['name'] = $this->checkAndUpdateScheduleName($inputs, $schedule);
            $schedule->fill($inputs);
            if ($schedule->save()) {
                DB::commit();
                return $this->success(__('schedule.updated'));
            }
            DB::rollback();
            return $this->error(__('schedule.update'), ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function storeGraph(StoreGraphRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();

            $schedule = $this->schedule->newQuery()->whereId($inputs['id'])->first();
            if($request->hasFile('graph'))
            {
                $this->deleteFile($schedule->graph);
                $this->uploadFile(request('graph'), $schedule, 'graph', false, 'user-'.auth()->user()->id."-schedules");
            }

            if ($schedule->save()) {
                DB::commit();
                return $this->successWithData(__('schedule.updated'), $schedule);
            }
            DB::rollback();
            return $this->error(__('schedule.update'), ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function update(UpdateRequest $request)
    {
        try {
            $changesCheck = false;
            DB::beginTransaction();
            $inputs = $request->all();
            if (empty($inputs['group_id']) && empty($inputs['device_id'])) {
                return $this->error('the group id or device id is required', ERROR_400);
            }
            if (!empty($inputs['group_id']) && !empty($inputs['device_id'])) {
                return $this->error('Either group id or device id can be accepted', ERROR_400);
            }


            $schedule = $this->schedule->newQuery()->where('id', $inputs['id'])->first();
            $inputs['name'] = $this->checkAndUpdateScheduleName($inputs, $schedule);
            $schedule->fill($inputs);
            $schedule->mode = SCHEDULE_ADVANCED;

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
                    if ($this->awsCall->sendScheduleToAws($schedule, $relation)) {
                        DB::commit();
                        return $this->success(__('schedule.updated'));
                    } else {
                        DB::rollback();
                        return $this->error(__('schedule.update'), ERROR_400);
                    }
                }
                DB::commit();
                return $this->successWithData(__('schedule.updated'), $schedule);
            }
            DB::rollback();
            return $this->error(__('schedule.update'), ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }
    public function resend(ResendRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if (!empty($inputs['device_id'])) {
                $device = $this->device->newQuery()->where('id', $inputs['device_id'])->with('schedule')->first();
                if ($device->schedule && $this->awsCall->sendScheduleToAws($device->schedule, $device)) {
                    DB::commit();
                    return $this->success(__('schedule.updated'));
                } else {
                    DB::rollback();
                    return $this->error(__('schedule.update'), ERROR_400);
                }
            } else if (!empty($inputs['group_id'])) {
                $group = $this->group->newQuery()->where('id', $inputs['group_id'])->with('schedule')->first();
                if ($group->schedule && $this->awsCall->sendScheduleToAws($group->schedule, $group)) {
                    DB::commit();
                    return $this->success(__('schedule.updated'));
                } else {
                    DB::rollback();
                    return $this->error(__('schedule.update'), ERROR_400);
                }
            }
            return $this->error(__('schedule.update'), ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function delete(DeleteRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $schedule = $this->schedule->newQuery()->where('id', $inputs['id'])->first();
            $graph = $schedule->graph;
            if ($schedule->enabled) {
                return redirect()->back()->with('error', 'Uploaded schedule cannot be deleted.');
            } else if ($schedule->delete()) {
                $this->deleteFile($graph);
                DB::commit();
                return $this->success(__('schedule.deleted'));
            }
            DB::rollback();
            return $this->error(__('schedule.delete'), ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function updateEasyModeSchedule(UpdateEasyModeScheduleRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if (empty($inputs['group_id']) && empty($inputs['device_id'])) {
                return $this->error('the group id or device id is required', ERROR_400);
            }
            if (!empty($inputs['group_id']) && !empty($inputs['device_id'])) {
                return $this->error('Either group id or device id can be accepted', ERROR_400);
            }

            $inputs = $this->formatEasySlots($inputs);
            $schedule = $this->schedule->newQuery()->where('id', $inputs['id'])->first();
            $inputs['name'] = $this->checkAndUpdateScheduleName($inputs, $schedule);

            $schedule->fill($inputs);
            $schedule->mode = SCHEDULE_EASY;
            $schedule->slots = $this->convertEasyToAdvanceSlots($inputs);

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
                    if ($this->awsCall->sendScheduleToAws($schedule, $relation)) {
                        DB::commit();
                        return $this->success(__('schedule.updated'));
                    } else {
                        DB::rollback();
                        return $this->error(__('schedule.update'), ERROR_400);
                    }
                }
                DB::commit();
                return $this->successWithData(__('schedule.updated'), $schedule);
            }
            DB::rollback();
            return $this->error(__('schedule.update'), ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function formatEasySlots($inputs)
    {
        $inputs['easy_slots'] = [];
        $inputs['easy_slots']['sunrise'] = Carbon::parse($inputs['sunrise'])->format('H:i:s');
        $inputs['easy_slots']['sunset'] = Carbon::parse($inputs['sunset'])->format('H:i:s');
        $inputs['easy_slots']['value_a'] = $inputs['value_a'];
        $inputs['easy_slots']['value_b'] = $inputs['value_b'];
        $inputs['easy_slots']['value_c'] = $inputs['value_c'];
        $inputs['easy_slots']['ramp_time'] = $inputs['ramp_time'];
        unset($inputs['sunset'], $inputs['sunrise'], $inputs['ramp_time'], $inputs['value_a'], $inputs['value_b'], $inputs['value_c']);
        return $inputs;
    }

    public function convertEasyToAdvanceSlots($inputs)
    {
        $rampHour = (int) head(explode(':', $inputs['easy_slots']['ramp_time']));
        $rampMinutes = (int) last(explode(':', $inputs['easy_slots']['ramp_time']));
        $slots = [];
        $slots[] = [
            'value_a' => "0",
            'value_b' => "0",
            'value_c' => "0",
            'start_time' => Carbon::parse($inputs['easy_slots']['sunrise'])->format('H:i:s'),
            'type' => TYPE_STEP
        ];
        $slots[] = [
            'value_a' => "0",
            'value_b' => "0",
            'value_c' => "0",
            'start_time' => Carbon::parse($inputs['easy_slots']['sunrise'])->addSecond()->format('H:i:s'),
            'type' => TYPE_GRADUAL
        ];
        $slots[] = [
            'value_a' => $inputs['easy_slots']['value_a'],
            'value_b' => $inputs['easy_slots']['value_b'],
            'value_c' => $inputs['easy_slots']['value_c'],
            'start_time' => Carbon::parse($inputs['easy_slots']['sunrise'])->addHours($rampHour)->addMinutes($rampMinutes)->format('H:i:s'),
            'type' => TYPE_STEP
        ];
        $slots[] = [
            'value_a' => $inputs['easy_slots']['value_a'],
            'value_b' => $inputs['easy_slots']['value_b'],
            'value_c' => $inputs['easy_slots']['value_c'],
            'start_time' => Carbon::parse($inputs['easy_slots']['sunset'])->addSecond()->format('H:i:s'),
            'type' => TYPE_GRADUAL
        ];
        $slots[] = [
            'value_a' => "0",
            'value_b' => "0",
            'value_c' => "0",
            'start_time' => Carbon::parse($inputs['easy_slots']['sunset'])->addHours($rampHour)->addMinutes($rampMinutes)->addSecond()->format('H:i:s'),
            'type' => TYPE_STEP
        ];
        $slots[] = [
            'value_a' => "0",
            'value_b' => "0",
            'value_c' => "0",
            'start_time' => Carbon::parse($inputs['easy_slots']['sunset'])->addHours($rampHour)->addMinutes($rampMinutes)->addSeconds(2)->format('H:i:s'),
            'type' => TYPE_STEP
        ];
        $slots = array_values(collect($slots)->sortBy('start_time')->toArray());
        return $slots;
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
