<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Aquarium;
use App\Models\CommandLog;
use App\Models\Device;
use App\Models\DeviceHistory;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Doctrine\DBAL\Query\QueryException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use PhpMqtt\Client\Facades\MQTT;
use Tymon\JWTAuth\Facades\JWTAuth;
use Ixudra\Curl\Facades\Curl;
use Auth;

class UserController extends Controller
{
    private $user, $aquarium, $schedule;

    public function __construct()
    {
        $this->user = new User();
        $this->schedule = new Schedule();
    }

    public function listing(Request $request)
    {
        $inputs = $request->all();
        $query = $this->user->newQuery()->where('role_id', USER_APP);
        $countries = $this->user->newQuery()->whereNotNull('country')->distinct('country')->pluck('country')->toArray();
        if (!empty($inputs['search'])) {
            $query->where(function ($q) use ($inputs) {
                searchTable($q, $inputs['search'], ['first_name', 'middle_name', 'last_name', 'username', 'email', 'created_at']);
            });
        }
        if(isset($inputs['status']))
        {
            $query->whereStatus($inputs['status']);
        }
        if(isset($inputs['country']))
        {
            $query->whereCountry($inputs['country']);
        }
        $users = $query->orderBy('created_at', 'DESC')->paginate(PAGINATE);
        return view('admin.users.listing', compact('users', 'countries'));
    }

    public function details($id)
    {
        $user = $this->user->newQuery()->where('id', $id)->with('products', 'aquaria', 'groups', 'devices')->first();
        $schedules = $this->schedule->newQuery()->where('user_id', $id)->with(['geolocation', 'user'])->get();
        // dd($schedules[1]);
        return view('admin.users.details', compact('user', 'schedules'));
    }

    public function changeStatus($id)
    {
        try {
            DB::beginTransaction();
            if ($user = $this->user->newQuery()->where('id', $id)->first()) {
                $user->status = ($user->status == STATUS_ACTIVE ? STATUS_DEACTIVE : STATUS_ACTIVE);
                if ($user->save()) {
                    DB::commit();
                    return redirect()->back()->with('success', 'User status updated successfully');
                }
            }
            DB::rollBack();
            return redirect()->back()->with('error', 'User not found.');
        } catch (QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error while activating user. Please try again later.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error while activating user. Please try again later.');
        }
    }

    public function updateOta($id)
    {
        $device = Device::find($id);

        if (!$device) {
            return redirect()->back()->with('error', 'Device not found');
        }
        $topic = $device->topic;
        $message = [
            "commandID" => 6, 'deviceID' => '1', "authApi" => url('api/iot-configuration-authenticate'),
            "macAddress" => $device->mac_address, "isGroup" => $device->group_id ? false : true, "timestamp" => (string) strtotime(Carbon::now()->setTimezone($device->timezone)->format('Y-m-d H:i:s'))
        ];
        MQTT::publish($topic, json_encode($message));
        MQTT::disconnect();
        return redirect()->back();
    }
    public function allUpdateOta()
    {
        $devices = Device::all();
        foreach($devices AS $device)
        {
            $topic = $device->topic;
            $message = [
                "commandID" => 6, 'deviceID' => '1', "authApi" => url('api/iot-configuration-authenticate'),
                "macAddress" => $device->mac_address, "isGroup" => $device->group_id ? false : true, "timestamp" => (string) strtotime(Carbon::now()->setTimezone($device->timezone)->format('Y-m-d H:i:s'))
            ];
            MQTT::publish($topic, json_encode($message));
        }

        MQTT::disconnect();
        return redirect()->back();
    }

    public function deleteDevice($id)
    {

        $device = Device::find($id);
        if ($device->delete()) {
            $message = [
                "commandID" => 2, "deviceID" => $device->id, "macAddress" => $device->mac_address, "isGroup" => false, "timestamp" => (string) now()
            ];
            MQTT::publish($device->topic, json_encode($message));
            MQTT::disconnect();
            return redirect()->back();
        }
    }

    public function passwordChange(Request $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if ($user = $this->user->newQuery()->whereId($inputs['id'])->first()) {
                $user->password = Hash::make($inputs['password']);
                if ($user->save()) {
                    DB::commit();
                    return redirect()->back()->with('success', 'Password Changed Successfully');
                }
            }
            DB::rollBack();
            return redirect()->back()->with('error', 'User not found.');
        } catch (QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error while activating user. Please try again later.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error while activating user. Please try again later.');
        }
    }

    public function deleteAccount(Request $request)
    {
        try {
            DB::beginTransaction();
            if ($user = $this->user->newQuery()->whereId(Auth::id())->first()) {

                // if($user->login_type == LOGIN_APPLE){
                //     $data = [];
                //     $data['client_id'] = "login.dalua.app";
                //     $data['client_secret'] = "eyJraWQiOiI4NjRENEpLQkhRIiwiYWxnIjoiRVMyNTYifQ.eyJpc3MiOiJRRjhKUEQ3R1cyIiwiaWF0IjoxNjYxNDI3ODMyLCJleHAiOjE2NzY5Nzk4MzIsImF1ZCI6Imh0dHBzOi8vYXBwbGVpZC5hcHBsZS5jb20iLCJzdWIiOiJsb2dpbi5kYWx1YS5hcHAifQ.6-7tIHJGlPORBhOdUyxN9_Lzl4ci-0m9fwYFqaDQH7TzLc_Y9taeydx8kVeYUeA-40sbUFlKCkYCEtK6mKAJ2g";
                //     $data['token'] = $user->social_token;
                //     $data['token_type_hint'] = 'access_token';
                //     $response = Curl::to("https://appleid.apple.com/auth/revoke")
                //                         ->asJsonResponse()
                //                         ->withData($data)
                //                         ->post();


                //     Log::info('error => ' . json_encode($response));
                //     if ($response->error) {

                //         Log::info('error => ' . $response->error);
                //         DB::rollBack();
                //         return $this->error($response->error, ERROR_400);
                //     }
                // }

                $user->devices()->delete();
                $user->groups()->delete();
                $aquariumIDs = $user->aquaria()->pluck('id');
                DB::table('user_aquaria')->whereIn('aquarium_id', $aquariumIDs)->delete();
                DB::table('user_aquaria')->whereUserId($user->id)->delete();
                $user->products()->delete();
                $user->aquaria()->delete();
                $user->schedules()->delete();
                CommandLog::whereUserId($user->id)->delete();
                DeviceHistory::whereUserId($user->id)->delete();
                $token = $request->header('Authorization');
                if($token)
                {
                    JWTAuth::invalidate($token);
                }

                $user->delete();
                DB::commit();
                return $this->success('User account has been deleted successfully');
            }
            DB::rollBack();
            return $this->error('User not found.', ERROR_400);
        } catch (QueryException $e) {
            Log::info('error => ' . $e->getMessage());
            DB::rollBack();
            return $this->error('Error while deleting user. Please try again later.', ERROR_400);
        } catch (Exception $e) {
            Log::info('error => ' . $e->getMessage());
            DB::rollBack();
            return $this->error('Error while deleting user. Please try again later.', ERROR_400);
        }
    }
}
