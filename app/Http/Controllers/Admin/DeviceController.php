<?php

namespace App\Http\Controllers\Admin;

use App\Drivers\AwsCall;
use App\Http\Controllers\Controller;
use App\Models\CommandLog;
use App\Models\Device;
use App\Models\DeviceHistory;
use App\Models\Product;
use App\Models\Schedule;
use Doctrine\DBAL\Query\QueryException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeviceController extends Controller
{
    private $device, $product, $schedule, $deviceHistory, $commandLog;
    public function __construct()
    {
        $this->device = new Device();
        $this->schedule = new Schedule();
        $this->product = new Product();
        $this->deviceHistory = new DeviceHistory();
        $this->commandLog = new CommandLog();
    }

    public function listing(Request $request)
    {
        $inputs = $request->all();
        $query = $this->device->newQuery()->orderBy('created_at', 'DESC')->with(['group', 'aquarium', 'user']);
        $products = $this->product->newQuery()->get();
        if (!empty($inputs['search'])) {
            $query->where(function ($q) use ($inputs) {
                searchTable($q, $inputs['search'], ['name', 'topic', 'mac_address']);
                searchTable($q, $inputs['search'], ['name', 'model', 'specification'], 'product');
                searchTable($q, $inputs['search'], ['name'], 'aquarium');
                searchTable($q, $inputs['search'], ['name', 'topic'], 'group');
                searchTable($q, $inputs['search'], ['first_name', 'last_name'], 'user');
            });
        }
        if(!empty($inputs['water_type']))
        {
            $query->whereWaterType($inputs['water_type']);
        }
        if(!empty($inputs['product_id']))
        {
            $query->whereProductId($inputs['product_id']);
        }
        $devices = $query->paginate(PAGINATE);
        return view('admin.devices.listing', compact('devices', 'products'));
    }

    public function detail($id)
    {
        $device = $this->device->newQuery()->whereId($id)
            ->with(['user', 'group', 'product', 'aquarium', 'settings', 'users', 'schedule'])
            ->first();
        $schedules = $this->schedule->newQuery()->whereDeviceId($id)->get();
        $topicLogs = $this->deviceHistory->newQuery()->where('device_id', $id)->where('type', LOG_TYPE_TOPIC)->orderBy('id', 'DESC')->paginate(PAGINATE);
        $connectivityLogs = $this->deviceHistory->newQuery()->where('device_id', $id)->where('type', LOG_TYPE_CONNECTIVITY)->orderBy('id', 'DESC')->paginate(PAGINATE);
        $subscribeLogs = $this->deviceHistory->newQuery()->where('device_id', $id)->where('type', LOG_TYPE_SUBSCRIBE)->orderBy('id', 'DESC')->paginate(PAGINATE);
        $commands = $this->commandLog->newQuery()->whereCommandId(4)->whereDeviceId($id)->orderBy('id', 'DESC')->get();
        return view('admin.devices.detail', compact('device', 'schedules', 'topicLogs', 'connectivityLogs', 'subscribeLogs', 'commands'));
    }

    public function delete(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $device = $this->device->newQuery()->whereId($id)->first();
            if ($device->delete()) {
                DB::commit();
                return redirect()->route('admin.devices.listing')->with('success', 'Deleted Successfully');
            }
            DB::rollback();
            return redirect()->back()->with('error', 'Error while uploading file');
        } catch (QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function instantControl(Request $request)
    {
        $device = $this->device->newQuery()->whereId($request->id)->first();

        $awsCall = new AwsCall();

        $message = [
            "commandID" => 3, "deviceID" => $device->uniqid, "macAddress" => $device->mac_address, "isGroup" => true, "timestamp" => now(),
            "a_value" => $request->c_value,
            "b_value" => $request->b_value,
            "c_value" => $request->a_value,
        ];
        $awsCall->publishTopic($device->topic, $message);

        return response()->json(['success' => true]);
    }

}
