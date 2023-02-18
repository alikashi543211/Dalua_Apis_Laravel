<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IotDeviceFileV4;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeviceConfigurationFileV4Controller extends Controller
{
    private $deviceConfig;

    public function __construct()
    {
        $this->deviceConfig = new IotDeviceFileV4();
    }

    public function listing()
    {
        $deviceConfigs = $this->deviceConfig->newQuery()->orderBy('created_at', 'DESC')->paginate(PAGINATE);
        return view('admin.iot-config-files.v4.listing', compact('deviceConfigs'));
    }

    public function add()
    {
        return view('admin.iot-config-files.v4.add');
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $config = $this->deviceConfig->newQuery()->orderBy('version', 'DESC')->first();
            $version = $config ? $config->version + 1 : 1;
            $deviceConfig = $this->deviceConfig->newInstance();
            $deviceConfig->name = $request->file('file')->getClientOriginalName();
            $deviceConfig->version = $version;
            $this->uploadFile(request('file'), $deviceConfig, 'location', true);
            if ($deviceConfig->save()) {
                DB::commit();
                return redirect()->route('admin.iotFile.v4.listing');
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
}
