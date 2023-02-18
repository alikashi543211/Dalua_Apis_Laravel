<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IotConfigration\VersionLogRequest;
use App\Models\Device;
use App\Models\IotDeviceFile;
use App\Models\IotLog;
use Carbon\Carbon;
use Doctrine\DBAL\Query\QueryException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class IotConfigurationFileController extends Controller
{
    private $deviceFile, $iotLog, $device;

    public function __construct()
    {
        $this->deviceFile = new IotDeviceFile();
        $this->device = new Device();
        $this->iotLog = new IotLog();
    }

    public function authenticate(Request $request)
    {
        try {
            Log::info("Request IP => " . $request->ip() . ", data => " . json_encode($request->all()));
            DB::beginTransaction();
            $inputs = $request->all();
            if (!empty($inputs['password'])) {
                Log::info("Have Password: true");
                if (Hash::check($inputs['password'], env('IOT_PASSWORD'))) {
                    Log::info("Pass Password: true");
                    if(isset($inputs['type'])){
                        $iotFile = $this->deviceFile->newQuery()->whereHas('product', function($q) use ($inputs){ $q->where('name', $inputs['type']); })->orderBy('id', 'DESC')->first();
                        if(!$iotFile){
                            $iotFile = $this->deviceFile->newQuery()->whereHas('product', function($q) use ($inputs){ $q->where('name', 'BlazeX'); })->orderBy('id', 'DESC')->first();
                        }
                    }else{
                        $iotFile = $this->deviceFile->newQuery()->orderBy('id', 'DESC')->first();
                    }

                    if ($iotFile) {
                        Log::info("Found OTA file: true");
                        $iotFile->token = Str::random(40);
                        if ($iotFile->save()) {
                            Log::info("Response OTA => " . json_encode(['url' => url('api/iot-updated-file?token=' . $iotFile->token), 'version' => $iotFile->version]));
                            DB::commit();
                            return $this->successWithData('File Found', ['url' => url('api/iot-updated-file?token=' . $iotFile->token), 'version' => $iotFile->version]);
                        } else {
                            Log::info("OTA file save failed");
                            DB::rollback();
                            return $this->error('Error while fetching token. Please try again later', ERROR_400);
                        }
                    } else {
                        return $this->error('No Device configuration found. Please try again later', ERROR_400);
                    }
                } else {
                    DB::rollback();
                    return $this->error('Invalid Password', ERROR_400);
                }
            } else {
                DB::rollback();
                return $this->error('Password is required', ERROR_400);
            }
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function getUpdatedFile(Request $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if (!empty($inputs['token'])) {
                if ($deviceFile = $this->deviceFile->newQuery()->where('token', $inputs['token'])->first()) {
                    if (strtotime($deviceFile->updated_at) > strtotime('-30 minutes')) {
                        if (env('AWS_ENV')) {
                            return Storage::disk('s3')->download($deviceFile->getRawOriginal('location'), $deviceFile->name);
                        }
                        return response()->download($deviceFile->location, $deviceFile->name);
                    } else {
                        DB::rollback();
                        return $this->error('Token is expired', ERROR_400);
                    }
                } else {
                    DB::rollback();
                    return $this->error('Invalid Token', ERROR_400);
                }
            }
            DB::rollback();
            return $this->error('Token is required', ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function updateLog(Request $request)
    {
        try {
            $inputs = $request->all();
            if(sizeof($inputs) != 0)
            {
                Log::info("IOT-Logs: " . json_encode($inputs));
                $iotLog = $this->iotLog->newInstance();
                $iotLog->log = json_encode($inputs);
                $iotLog->save();
            }
            return $this->success("Data updated successfully");
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function getLogs()
    {
        $iotLogs = $this->iotLog->newQuery()->get();
        return $this->successWithData("Data fetched successfully", $iotLogs);
    }

    public function versionLog(VersionLogRequest $request)
    {
        try {
            $inputs = $request->all();
            $device = $this->device->newQuery()->whereMacAddress($inputs['mac_address'])->first();
            if($device)
            {
                sleep(2);
                $device->version = $inputs['version'];

                if(!$device->save())
                {
                    DB::rollback();
                    return $this->error(GENERAL_ERROR_MESSAGE, ERROR_400);
                }
            }
            return $this->success("Data saved successfully");
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }
    public function getOTAFilesForMobiles(Request $request)
    {
        try {
            $inputs = $request->all();

            $blazx = $this->deviceFile->newQuery()->whereHas('product', function($q) { $q->where('name', 'BlazeX'); })->with('product')->orderBy('id', 'DESC')->first();
            $x4 = $this->deviceFile->newQuery()->whereHas('product', function($q) { $q->where('name', 'X4'); })->with('product')->orderBy('id', 'DESC')->first();
            $data = [];
            if($blazx){

                $blazx->token = Str::random(40);
                $blazx->save();
                $blazx->url = url('api/iot-updated-file?token=' . $blazx->token);

                $data[] = $blazx;
            }
            if($x4){

                $x4->token = Str::random(40);
                $x4->save();
                $x4->url = url('api/iot-updated-file?token=' . $x4->token);
                $data[] = $x4;
            }
            return $this->successWithData(GENERAL_SUCCESS_MESSAGE, $data);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }
}
