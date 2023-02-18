<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IotDeviceFileV4;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class IotConfigurationFileV4Controller extends Controller
{
    private $deviceFile;

    public function __construct()
    {
        $this->deviceFile = new IotDeviceFileV4();
    }

    public function authenticate(Request $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if (!empty($inputs['password'])) {
                if (Hash::check($inputs['password'], env('IOT_PASSWORD'))) {
                    if ($iotFile = $this->deviceFile->newQuery()->orderBy('version', 'DESC')->first()) {
                        $iotFile->token = Str::random(40);
                        if ($iotFile->save()) {
                            DB::commit();
                            return $this->successWithData('File Found', ['url' => url('api/iot-updated-file-dev-v4?token=' . $iotFile->token), 'version' => $iotFile->version]);
                        } else {
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
}
