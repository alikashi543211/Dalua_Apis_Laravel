<?php

namespace App\Http\Controllers\Api;

use App\Drivers\AwsCall;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Device\ChangeTypeRequest;
use App\Http\Requests\Api\Device\CheckMacAddressesRequest;
use App\Http\Requests\Api\Device\DeleteRequest;
use App\Http\Requests\Api\Device\DeviceAcknowledgeRequest;
use App\Http\Requests\Api\Device\GetDeviceDetailsRequest;
use App\Http\Requests\Api\Device\InstantShowRequest;
use App\Http\Requests\Api\Device\StatusRequest;
use App\Http\Requests\Api\Device\StoreRequest;
use App\Http\Requests\Api\Device\UpdateRequest;
use App\Http\Requests\Api\Group\ListingRequest;
use App\Models\CommandLog;
use App\Models\Device;
use App\Models\DeviceHistory;
use App\Models\DeviceSetting;
use App\Models\Group;
use App\Models\IotDeviceConfiguration;
use App\Models\Product;
use App\Models\Schedule;
use App\Traits\Api\DeviceTrait;
use Carbon\Carbon;
use Doctrine\DBAL\Query\QueryException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpMqtt\Client\Facades\MQTT;

class DeviceController extends Controller
{
    use DeviceTrait;
    private $device, $schedule, $deviceHistory, $deviceSetting, $mqtt, $group, $aws, $commandLog, $product;

    public function __construct()
    {
        $this->device = new Device();
        $this->schedule = new Schedule();
        $this->group = new Group();
        $this->deviceSetting = new DeviceSetting();
        $this->aws = new AwsCall();
        $this->product = new Product();
        $this->commandLog = new CommandLog();
        $this->deviceHistory = new DeviceHistory();
    }

    public function setDefaultProdect($device)
    {
        $product = $this->product->newQuery()->whereName('BlazeX')->first();
        if($product)
        {
            $device->product_id = $product->id;
        }
        return $device;
    }

    public function store(StoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $device = $this->device->newInstance();
            $device->completed = 0;
            $device->fill($inputs);
            $device->timezone = $inputs['timezone'];

            if(!empty($inputs['product']))
            {
                Log::info("product => " . $inputs['product']);
                $product = $this->product->newQuery()->whereName($inputs['product'])->first();
                if($product)
                {
                    $device->product_id = $product->id;
                }else{
                    $device = $this->setDefaultProdect($device);
                }
            }else{
                $device = $this->setDefaultProdect($device);
            }
            Log::info("product ID => " . $device->product_id);
            $device->user_id = Auth::id();
            $device->status = 0;
            if (!empty($inputs['water_type'])) {
                $device->water_type = $inputs['water_type'];
                $device->configuration = $this->AttachDeviceConfiguration($inputs);
            }
            if ($device->save()) {
                $device = $device->fresh();

                $device->topic = str_replace(' ', '', Str::lower(Auth::user()->first_name) . '-' . Auth::id()) . '/device/' . $device->uid;
                if ($device->save()) {
                    $deviceSetting = $this->deviceSetting->newInstance();
                    $deviceSetting->device_id = $device->id;
                    if ($deviceSetting->save()) {
                        if ($this->saveDeviceHistory($device, "Topic set to {$device->topic}")); {
                            $device = $this->getMyDeviceDetail($device->id);
                            DB::commit();
                            return $this->successWithData(__('device.added'), $device);
                        }
                    }
                }
            }
            DB::rollback();
            return $this->error(__('device.add'), ERROR_400);
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
            $grouped = DEVICE_UPDATE_NOTHING; // 1 nothing changed, 2 = device removed from group, 3 = device added to the group
            $deviceGroupId = null;
            DB::beginTransaction();
            $inputs = $request->all();

            $device = $this->device->newQuery()->where('id', $inputs['id'])->first();
            $oldTopic = $device->topic;
            if ($device->group && isset($inputs['water_type'])) {
                DB::rollback();
                return $this->error(WATER_TYPE_NOT_UPDATED, ERROR_400);
            }
            if ($device->group_id && empty($inputs['group_id'])) {
                if($device->status != 1)
                {
                    DB::rollback();
                    return $this->error(DEVICE_REMOVE_DISCONNECT_MESSAGE, ERROR_400);
                }
                $deviceGroupId = $device->group_id;
                $grouped = DEVICE_REMOVED_FROM_GROUP;
                $groupTopic = $device->group->topic;
                $device->group_id = NULL;
            } else if (!$device->group_id && !empty($inputs['group_id'])) {
                if($device->status != 1)
                {
                    DB::rollback();
                    return $this->error(DEVICE_ADD_DISCONNECT_MESSAGE, ERROR_400);
                }
                $result = $this->isGroupTypeSameAsDeviceType($inputs);
                if (!$result[0]) {
                    DB::rollback();
                    return $this->error($result[1], ERROR_400);
                }
                $grouped = DEVICE_ADDED_TO_GROUP;
            }
            $device->fill($inputs);
            if(!empty($inputs['product']))
            {
                $product = $this->product->newQuery()->whereName($inputs['product'])->first();
                if($product)
                {
                    $device->product_id = $product->id;
                }
            }
            if (!empty($inputs['water_type'])) {
                $device->water_type = $inputs['water_type'];
                $device->configuration = $this->AttachDeviceConfiguration($inputs);
            } elseif ($device->water_type) {
                $device->configuration = $this->AttachDeviceConfiguration($inputs, $device->water_type);
            }
            if ($device->save()) {
                if ($deviceGroupId) {
                    if (!$this->setGroupType($deviceGroupId)) {
                        DB::rollback();
                        return $this->error(__('device.update'), ERROR_400);
                    }
                }
                if (!empty($inputs['water_type']))
                {
                    if(!$this->isWaterTypeUpdatedForAllSchedules($device))
                    {
                        DB::rollback();
                        return $this->error(__('device.update'), ERROR_400);
                    }
                }

                DB::commit();
                $device = $device->fresh();
                if ($grouped == DEVICE_ADDED_TO_GROUP) {
                    $schedule = $device->group->schedule; // group schedule
                    $relation = $device->group; // group relation
                    $this->aws->sendScheduleToAws($schedule, $relation, $device->getRawOriginal('topic')); // send group schedule to device topic so before we change the topic device can have group schedule
                    // now need to send change topic to device on device topic with new group topic
                    sleep(2); // with 2 second delay so schedule successfuly updated before we send change topic command
                    $message = [
                        "commandID" => 5, "deviceID" => $device->uniqid, "macAddress" => $device->mac_address, "isGroup" => false, "timestamp" => now(), "topic" => $device->topic
                    ];
                    $this->aws->publishTopic($device->getRawOriginal('topic'), $message);
                } else if ($grouped == DEVICE_REMOVED_FROM_GROUP) {
                    $schedule = $device->schedule; // device schedule
                    $relation = $device;  // device relation
                    $this->aws->sendScheduleToAws($schedule, $relation, $groupTopic); // send device schedule to group topic so before we change the topic device can have device schedule
                    // now need to send change topic to device on group topic with device topic
                    sleep(2); // with 2 second delay so schedule successfuly updated before we send change topic command
                    $message = [
                        "commandID" => 5, "deviceID" => $device->uniqid, "macAddress" => $device->mac_address, "isGroup" => false, "timestamp" => now(), "topic" => $device->topic
                    ];
                    $this->aws->publishTopic($groupTopic, $message);
                }

                if ($this->saveDeviceHistory($device, "Topic changed from $oldTopic to {$device->topic}")) {
                    $device = $this->getMyDeviceDetail($device->id);
                    DB::commit();
                    return $this->successWithData(__('device.updated'), $device);
                }
            }
            DB::rollback();
            return $this->error(__('device.update'), ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    private function deviceGroupUpdate($device, $grouped, $groupTopic, $schedule, $relation, $topic)
    {
        $message = [
            "commandID" => 5, "deviceID" => $device->uniqid, "macAddress" => $device->mac_address, "isGroup" => false, "timestamp" => now(), "topic" => $device->topic
        ];
        if ($grouped == DEVICE_REMOVED_FROM_GROUP) {
            $topic = $groupTopic;
        }
        $this->aws->sendScheduleToAws($schedule, $relation, $topic);
        sleep(2);
        $this->aws->publishTopic($topic, $message);
    }

    public function listing(ListingRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $query = $this->device->newQuery()->where(function($q){
                $q->where('user_id', Auth::id())->orWhere(function($q){
                    $q->whereHas('aquarium', function($q) {
                        $q->whereHas('userAquariums', function($q){
                            $q->where('user_aquaria.aquarium_id', request('aquarium_id'))->where('user_aquaria.user_id', Auth::id())->where('user_aquaria.status', SHARED_AQUARIUM_STATUS_ACCEPTED);
                        });
                    });

                });
            })->where('aquarium_id', $inputs['aquarium_id'])->with(['product']);
            if (!empty($inputs['search'])) {
                $query->where(function ($q) use ($inputs) {
                    searchTable($q, $inputs['search'], ['name', 'topic']);
                    searchTable($q, $inputs['search'], ['name', 'model', 'specification'], 'product');
                    searchTable($q, $inputs['search'], ['name'], 'aquarium');
                    searchTable($q, $inputs['search'], ['name', 'topic'], 'group');
                    searchTable($q, $inputs['search'], ['first_name', 'last_name'], 'user');
                });
            }
            if (!empty($inputs['group_id'])) {
                $query->where('group_id', $inputs['group_id']);
            } else {
                $query->whereDoesntHave('group');
            }
            $devices = $query->paginate(PAGINATE);
            return $this->successWithData(__('device.fetched'), $devices);
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
            $device = $this->device->newQuery()->where('id', $inputs['id'])->first();
            $groupId = NULL;
            if($device->group)
            {
                $groupId = $device->group->id;
            }
            if (!$device->delete()) {
                DB::rollback();
                return $this->error(__('device.delete'), ERROR_400);
            }
            if($groupId)
            {
                if($this->device->newQuery()->whereGroupId($groupId)->count() == 0)
                {
                    $group = $this->group->newQuery()->whereId($groupId)->first();
                    $group->water_type = NULL;
                    if(!$group->save())
                    {
                        DB::rollback();
                        return $this->error(__('device.delete'), ERROR_400);
                    }
                }
            }

            DB::commit();
            $message = [
                "commandID" => 2, "deviceID" => $device->id, "macAddress" => $device->mac_address, "isGroup" => false, "timestamp" => (string) strtotime(Carbon::now()->format('Y-m-d H:i:s'))
            ];
            $this->aws->publishTopic($device->topic, $message);
            return $this->success(__('device.deleted'));
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }
    public function updateStatus(StatusRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();

            $device = $this->device->newQuery()->where('id', $inputs['id'])->first();
            if($device->completed){

                if(!empty($inputs['ip_address'])){
                    $device->ip_address = $inputs['ip_address'];
                }
                $device->save();

                return $this->successWithData(__('device.updated'), $device->fresh());
            }
            $device->fill($inputs);
            $device->completed = 1;
            $device->status = 1;
            if(!empty($inputs['ip_address'])){
                $device->ip_address = $inputs['ip_address'];
            }

            if (!empty($inputs['water_type'])) {
                $device->water_type = $inputs['water_type'];
                $device->configuration = $this->AttachDeviceConfiguration($inputs);
            }
            if ($device->save()) {
                $device = $device->fresh();
                $this->createDefaultScedule(DEFAULT_SCHEDULE_DEVICE, $device);

                if ($device->group) {
                    $result = $this->isGroupTypeSameAsDeviceTypeUpdateStatus($inputs, $device->group_id);
                    if (!$result[0]) {
                        DB::rollback();
                        return $this->error($result[1], ERROR_400);
                    }
                }
                $device = $device->fresh();
                if ($device->group_id) {
                    $schedule = $device->group->schedule;
                    $relation = $device->group;
                    if (!$device->group->water_type) {
                        $device->group->water_type = $device->water_type;
                        $device->group->configuration = $this->AttachDeviceConfiguration($inputs, $device->water_type);
                        $device->group->save();
                    }
                } else {
                    $schedule = $device->schedule;
                    $relation = $device;
                }
                DB::commit();
                $this->aws->sendScheduleToAws($schedule, $relation);
                // ota request
                sleep(4);
                // $topic = $device->topic;
                // $message = [
                //     "commandID" => 6, 'deviceID' => '1', "authApi" => url('api/iot-configuration-authenticate'),
                //     "macAddress" => $device->mac_address, "isGroup" => $device->group_id ? false : true, "timestamp" => (string) strtotime(Carbon::now()->setTimezone($device->timezone)->format('Y-m-d H:i:s'))
                // ];
                // MQTT::publish($topic, json_encode($message));
                // MQTT::disconnect();
                $device = Device::whereId($device->id)->with('group', 'product', 'aquarium', 'settings', 'schedule')->first();
                return $this->successWithData(__('device.updated'), $device);
            }
            DB::rollback();
            return $this->error(__('device.update'), ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function getDeviceDetails(GetDeviceDetailsRequest $request)
    {
        $inputs = $request->all();
        $device = $this->device->newQuery()->where('id', $inputs['id'])->with('group', 'product', 'aquarium', 'settings', 'schedule')->first();
        return  $this->successWithData(__('device.details'), $device);
    }

    public function instantShow(InstantShowRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if (!$device = $this->group->newQuery()->where('user_id', Auth::id())->where('uid', $inputs['uid'])->first()) {
                if (!$device = $this->device->newQuery()->where('user_id', Auth::id())->where('uid', $inputs['uid'])->first()) {
                    return $this->error('Invalid UID', ERROR_400);
                }
            }
            $topic =  $device->group ? $device->group->topic : $device->topic;
            if ($this->updateThingViaMqtt($inputs, $topic)) {
                DB::commit();
                return $this->success(__('device.instantShowUpdated'));
            }
            DB::rollback();
            return $this->error(__('device.instantShowUpdate'), ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    private function updateThingViaMqtt($inputs, $topic)
    {
        $message = [
            'a_value' => $inputs['value_a'],
            'b_value' => $inputs['value_b'],
            'c_value' => $inputs['value_c']
        ];
        $this->aws->publishTopic($topic, $message);
        return true;
    }

    public function CheckMacAddresses(CheckMacAddressesRequest $request)
    {
        try {
            $inputs = $request->all();
            $response = [];
            foreach ($inputs['mac_addresses'] as $key => $macAddress) {
                $response[] = $this->device->newQuery()->where('mac_address', $macAddress)->exists();
            }

            Log::info('other mac_address => ' . json_encode($response));
            return  $this->successWithData(__('device.details'), $response);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }
    public function CheckMacAddressesMulti(CheckMacAddressesRequest $request)
    {
        try {
            $inputs = $request->all();
            $response = [];
            foreach ($inputs['mac_addresses'] as $key => $macAddress) {
                $response[$key]['mac'] = $macAddress;
                $response[$key]['status'] = $this->device->newQuery()->where('mac_address', $macAddress)->exists();
            }
            Log::info('mac_address => ' . json_encode($response));
            return  $this->successWithData(__('device.details'), $response);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }
    public function checkAckAgaisnttMacAddress(CheckMacAddressesRequest $request)
    {
        try {
            $inputs = $request->all();
            $response = [];
            $timestamp = strtotime(Carbon::now()->format('Y-m-d H:i:s'));
            foreach ($inputs['mac_addresses'] as $key => $macAddress) {
                $log = CommandLog::where('mac_address', $macAddress)->orderby('id', 'DESC')->first();
                $response[$key]['fromDevice'] = true;
                $response[$key]['macAddress'] = $macAddress;
                $response[$key]['commandID'] = 4;
                $response[$key]['timestamp'] = $log ? $log->timestamp : (string) $timestamp;
                $response[$key]['status'] = $log ? $log->status : 0;
            }

            return  $this->successWithData(__('device.details'), $response);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function deviceAcknowledge(DeviceAcknowledgeRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $commandLog = $this->commandLog->newInstance();
            $commandLog->topic = $inputs['topic'];
            $commandLog->timestamp = $inputs['timestamp'];
            $commandLog->command_id = $inputs['command_id'];
            $commandLog->response = $inputs['response'];
            $commandLog->device_id = !empty($inputs['device_id']) ? $inputs['device_id'] : NULL;
            $commandLog->group_id = !empty($inputs['group_id']) ? $inputs['group_id'] : NULL;
            $commandLog->user_id = Auth::id();
            if ($commandLog->save()) {
                DB::commit();
                return $this->success('Command Logged Successfully');
            }
            DB::rollback();
            return $this->error('Error while logging command.', ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function checkStatus(GetDeviceDetailsRequest $request)
    {
        $inputs = $request->all();
        $query = $this->device->newQuery();
        if (empty($inputs['device_id']) && empty($inputs['group_id']) && empty($inputs['id'])) {
            return $this->error('Id Or Device id Or group id is required', ERROR_400);
        }
        if (!empty($inputs['id'])) {
            $data = $query->where('id', $inputs['id'])->first();
            $device = ['status' => $data->status];
        } elseif (!empty($inputs['device_id'])) {
            $data = $query->where('id', $inputs['device_id'])->first();
            $device = ['status' => $data->status];
        } elseif (!empty($inputs['group_id'])) {
            $device = ['status' => 1];
            if ($query->where('group_id', $inputs['group_id'])->whereStatus(0)->exists()) {
                $device = ['status' => 0];
            }
        }
        return  $this->successWithData(__('device.details'), $device);
    }

    public function changeProduct(ChangeTypeRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            Log::info('change => '. json_encode($inputs));
            sleep(2);
            $device = $this->device->newQuery()->where('id', $inputs['id'])->first();
            $device->product_id = $inputs['product_id'];

            $device->status = 1;

            if ($device->save()) {
                DB::commit();

                return $this->successWithData(__('device.updated'), $device->fresh());
            }
            DB::rollback();
            return $this->error(__('device.update'), ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }
}
