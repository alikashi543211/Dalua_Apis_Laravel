<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\CommandLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeviceConfigurationFileController;
use App\Http\Controllers\Admin\DeviceConfigurationFileV4Controller;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DeviceController;
use App\Http\Controllers\Admin\DeviceHistoryController;
use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProfileController;
use App\Models\Device;
use App\Models\GeoLocation;
use App\Models\Schedule;
use App\Models\WeatherConfiguration;
use Aws\Laravel\AwsFacade;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use PhpMqtt\Client\Facades\MQTT;
use Illuminate\Support\Facades\URL;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// URL::forceScheme('https');

Route::get('ota-request', function () {
    $mqtt = new Mqtt();
    $inputs = request()->all();
    $device = null;
    if (!empty($inputs['id'])) {
        $device = Device::where('id', $inputs['id'])->first();
    } else if (!empty($inputs['topic'])) {
        $device = Device::where('topic', $inputs['topic'])->first();
    }

    if (!$device) {
        return 'device not found';
    }
    $topic = $device->topic;
    $message = [
        "commandID" => 6, "authApi" => 'http://13.58.50.74/iot-backend/public/api/iot-configuration-authenticate', "macAddress" => $device->mac_address, "isGroup" => true, "timestamp" => now()
    ];
    $output = $mqtt->ConnectAndPublish($topic, json_encode($message));
    dd($output);
});
Route::get('test', function () {
    $schedules = Schedule::where('user_id', 1)->get();
    foreach ($schedules as $key => $schedule) {
        $slots = [];
        foreach ($schedule->slots as $k => $v) {
            $v->start_time = Carbon::parse($v->start_time)->format('H:i:s');
            $slots[] = $v;
        }
        $schedule->slots = $slots;
        $schedule->save();
    }
});

Route::get('/', function () {
    // $res = IotClient::createThing([
    //     "attributePayload" => [
    //         "attributes" => [
    //             "string" => "string"
    //         ]
    //     ],
    //     "billingGroupName" => "string",
    //     "thingTypeName" => "string"
    // ]);
    // $s3 = AwsFacade::createClient('iot');
    // $res = $s3->createThing([
    //     "thingName" => "name1",
    //     "attributePayload" => [
    //         "attributes" => [
    //             "string" => "string"
    //         ]
    //     ],
    // ]);
});

Route::get('/', function () {
    return redirect()->route('admin.users.listing');
});

Route::prefix('admin')->group(function () {

    Route::middleware(['unAuthenticated'])->group(function () {
        Route::get('login', [LoginController::class, 'login'])->name('admin.login');
        Route::post('authenticate-user', [LoginController::class, 'authenticateUser'])->name('admin.authenticateUser');
    });

    Route::middleware(['authenticated'])->group(function () {
        Route::get('/', function () {
            return redirect()->route('admin.users.listing');
        });
        Route::get('logout', [LoginController::class, 'logout'])->name('admin.logout');
        Route::get('dashboard', [DashboardController::class, 'dashboard'])->name('admin.dashboard');
        // Users
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'listing'])->name('admin.users.listing');
            Route::get('details/{id}', [UserController::class, 'details'])->name('admin.users.details');
            Route::post('password-change', [UserController::class, 'passwordChange'])->name('admin.users.password.change');
            Route::get('change-status/{id}', [UserController::class, 'changeStatus'])->name('admin.users.changeStatus');
            Route::get('update-ota/{id}', [UserController::class, 'updateOta'])->name('admin.users.updateOta');
            Route::get('all-update-ota', [UserController::class, 'allUpdateOta'])->name('admin.users.allUpdateOta');
            Route::get('delete/{id}', [UserController::class, 'deleteDevice'])->name('admin.users.delete');
        });

        // Profile
        Route::prefix('profile')->group(function () {
            Route::get('edit', [ProfileController::class, 'edit'])->name('admin.profile.edit');
            Route::post('update', [ProfileController::class, 'update'])->name('admin.profile.update');
            Route::post('update-password', [ProfileController::class, 'updatePassword'])->name('admin.profile.password');
        });

        // Devices
        Route::prefix('devices')->group(function () {
            Route::get('/', [DeviceController::class, 'listing'])->name('admin.devices.listing');
            Route::get('detail/{id}', [DeviceController::class, 'detail'])->name('admin.devices.detail');
            Route::get('delete/{id}', [DeviceController::class, 'delete'])->name('admin.devices.delete');
            Route::post('instant/control', [DeviceController::class, 'instantControl'])->name('admin.devices.instantControl');
        });

        // Devices
        Route::prefix('device_histories')->group(function () {
            Route::get('/', [DeviceHistoryController::class, 'listing'])->name('admin.device_histories.listing');
        });

        // Groups
        Route::prefix('groups')->group(function () {
            Route::get('/', [GroupController::class, 'listing'])->name('admin.groups.listing');
            Route::get('detail/{id}', [GroupController::class, 'detail'])->name('admin.groups.detail');
            Route::post('instant/control', [GroupController::class, 'instantControl'])->name('admin.groups.instantControl');
        });

        // Roles
        Route::prefix('roles')->group(function () {
            Route::get('listing', [RoleController::class, 'listing'])->name('admin.roles.listing');
            Route::get('add', [RoleController::class, 'add'])->name('admin.roles.add');
            Route::post('store', [RoleController::class, 'store'])->name('admin.roles.store');
            Route::get('change-permission', [RoleController::class, 'changePermission'])->name('admin.roles.changePermission');
        });

        // Command Logs
        Route::prefix('command-logs')->group(function () {
            Route::get('listing', [CommandLogController::class, 'listing'])->name('admin.commandLogs.listing');
        });


        Route::prefix('schedules')->group(function () {
            Route::get('listing', [ScheduleController::class, 'listing'])->name('admin.schedules.listing');
            Route::get('requests', [ScheduleController::class, 'scheduleRequests'])->name('admin.schedules.requests');
            Route::get('public-requests', [ScheduleController::class, 'schedulePublicRequests'])->name('admin.schedules.public_requests');
            Route::get('update-approval', [ScheduleController::class, 'updateApproval'])->name('admin.schedules.update.approval');
            Route::get('listing-dalua', [ScheduleController::class, 'listingDalua'])->name('admin.schedules.listingDalua');
            Route::get('add', [ScheduleController::class, 'add'])->name('admin.schedules.add');
            Route::get('add-easy', [ScheduleController::class, 'addEasy'])->name('admin.schedules.addEasy');
            Route::post('store', [ScheduleController::class, 'store'])->name('admin.schedules.store');
            Route::post('store-easy', [ScheduleController::class, 'storeEasy'])->name('admin.schedules.storeEasy');
            Route::get('edit/{id}', [ScheduleController::class, 'edit'])->name('admin.schedules.edit');
            Route::get('edit-easy/{id}', [ScheduleController::class, 'editEasy'])->name('admin.schedules.editEasy');
            Route::post('update', [ScheduleController::class, 'update'])->name('admin.schedules.update');
            Route::post('update-easy', [ScheduleController::class, 'updateEasy'])->name('admin.schedules.updateEasy');
            Route::get('delete/{id}', [ScheduleController::class, 'delete'])->name('admin.schedules.delete');
            Route::get('get-schedule-data/{id}', [ScheduleController::class, 'getScheduleData'])->name('admin.schedule.getScheduleData');
            Route::get('get-easy-schedule-data/{id}', [ScheduleController::class, 'getEasyScheduleData'])->name('admin.schedule.getEasyScheduleData');
            Route::get('set-default/{id}', [ScheduleController::class, 'setDefaultSchedule'])->name('admin.schedule.setDefaultSchedule');
            Route::get('schedule/upload/{id}', [ScheduleController::class, 'scheduleUpload'])->name('admin.schedule.scheduleUpload');
            Route::post('schedule/store-graph-image', [ScheduleController::class, 'storeGraphImage'])->name('admin.schedules.ajax.store.graph.image');

        });

        Route::prefix('iot-files')->group(function () {
            Route::get('listing', [DeviceConfigurationFileController::class, 'listing'])->name('admin.iotFile.listing');
            Route::get('add', [DeviceConfigurationFileController::class, 'add'])->name('admin.iotFile.add');
            Route::post('store', [DeviceConfigurationFileController::class, 'store'])->name('admin.iotFile.store');
            Route::get('delete', [DeviceConfigurationFileController::class, 'delete'])->name('admin.iotFile.delete');


            // Route::prefix('v4')->group(function () {
            //     Route::get('listing', [DeviceConfigurationFileV4Controller::class, 'listing'])->name('admin.iotFile.v4.listing');
            //     Route::get('add', [DeviceConfigurationFileV4Controller::class, 'add'])->name('admin.iotFile.v4.add');
            //     Route::post('store', [DeviceConfigurationFileV4Controller::class, 'store'])->name('admin.iotFile.v4.store');
            // });
        });

        Route::prefix('products')->group(function () {
            Route::get('listing', [ProductController::class, 'listing'])->name('admin.products.listing');
            Route::get('add', [ProductController::class, 'add'])->name('admin.products.add');
            Route::get('edit/{id}', [ProductController::class, 'edit'])->name('admin.products.edit');
            Route::post('store', [ProductController::class, 'store'])->name('admin.products.store');
            Route::post('update', [ProductController::class, 'update'])->name('admin.products.update');
            Route::get('delete', [ProductController::class, 'delete'])->name('admin.products.delete');
        });
    });
});
