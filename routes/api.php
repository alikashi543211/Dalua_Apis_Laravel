<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Api\AquariumController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\GeolocationController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\ShareController;
use App\Http\Controllers\Api\ThingController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\IotConfigurationFileController;
use App\Http\Controllers\Api\IotConfigurationFileV4Controller;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Device;
use PhpParser\JsonDecoder;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('test', function () {
    $moon = new App\Drivers\Solaris();
    dd((int) $moon->get('age'));
    dd(json_decode('{"fromDevice":true,"macAddress":"2462ABFC3EE4","commandID":3,"status":1,"timestamp":"1640159346079"}'));
    dd(strtotime(now()));
    dd(Hash::make('ESP32@DevicePassword!'));
});
// Route::get('tinker', function () {
//     $inputs = request()->all();
//     dd($inputs['model']::where($inputs['where'], $inputs['where_value'])->{$inputs['set']}());
// });

Route::post('schedules/topic/subscribe', [ScheduleController::class, 'subscribe']);
Route::group([], function () {
    Route::get('ping-test', function () {
        return response()->json();
    });
    Route::prefix('auth')->group(function () {
        Route::post('register', [RegisterController::class, 'register']);
        Route::post('login', [LoginController::class, 'login']);
        Route::post('verify-code-resend', [RegisterController::class, 'verificationCodeResend']);
        Route::post('verify-email', [RegisterController::class, 'verifyEmailVerificationCode']);
        Route::post('new-user', [RegisterController::class, 'newUser']);
        Route::post('reset-password-mail', [LoginController::class, 'forgetPasswordMail']);
        Route::post('verify-reset-code', [LoginController::class, 'verifyResetCode']);
        Route::post('reset-password', [LoginController::class, 'resetPassword']);
    });
});

Route::middleware(['JWTAuth'])->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('logout', [LoginController::class, 'logout']);
    });

    Route::prefix('notifications')->group(function () {
        Route::post('update-token', [NotificationController::class, 'updateDeviceToken']);
    });
    Route::prefix('user')->group(function () {
        Route::post('delete-account', [UserController::class, 'deleteAccount']);
    });
    // name, email,tankssize,country password change,
    Route::prefix('profile')->group(function () {
        Route::post('update-profile', [ProfileController::class, 'updateProfile']);
        Route::post('update-notification-status', [ProfileController::class, 'updateNotificationStatus']);
        Route::post('update-password', [ProfileController::class, 'updatePassword']);
        Route::post('update-image', [ProfileController::class, 'updateImage']);
        Route::get('detail', [ProfileController::class, 'detail']);
    });

    // Aquarium Routes
    Route::prefix('aquariums')->group(function () {
        Route::post('listing', [AquariumController::class, 'listing']);
        Route::post('store', [AquariumController::class, 'store']);
        Route::post('update', [AquariumController::class, 'update']);
        Route::post('details', [AquariumController::class, 'details']);
        Route::post('delete', [AquariumController::class, 'delete']);
        Route::post('shared-users', [AquariumController::class, 'sharedUsers']);
        Route::post('approve-aquarium', [AquariumController::class, 'approveAquarium']);
        // Parameters
        Route::prefix('parameters')->group(function () {
            Route::post('store', [AquariumController::class, 'storeParameters']);
            Route::post('detail', [AquariumController::class, 'parametersDetail']);
            Route::post('listing', [AquariumController::class, 'listParameters']);
            Route::post('update', [AquariumController::class, 'updateParameters']);
            Route::post('delete', [AquariumController::class, 'deleteParameters']);
        });
    });

    // Category Routes
    Route::prefix('category')->group(function () {
        Route::post('listing', [CategoryController::class, 'listing']);
        Route::post('store', [CategoryController::class, 'store']);
        Route::post('update', [CategoryController::class, 'update']);
        Route::post('delete', [CategoryController::class, 'delete']);
    });

    // Product Routes
    Route::prefix('products')->group(function () {
        Route::post('listing', [ProductController::class, 'listing']);
        Route::post('store', [ProductController::class, 'store']);
        Route::post('update', [ProductController::class, 'update']);
        Route::post('delete', [ProductController::class, 'delete']);
    });

    // Group Routes
    Route::prefix('groups')->group(function () {
        Route::post('listing', [GroupController::class, 'listing']);
        Route::post('detail', [GroupController::class, 'detail']);
        Route::post('store', [GroupController::class, 'store']);
        Route::post('update', [GroupController::class, 'update']);
        Route::post('delete', [GroupController::class, 'delete']);
    });

    // Devices Routes
    Route::prefix('devices')->group(function () {
        Route::post('listing', [DeviceController::class, 'listing']);
        Route::post('store', [DeviceController::class, 'store']);
        Route::post('update', [DeviceController::class, 'update']);
        Route::post('status-update', [DeviceController::class, 'updateStatus']);
        Route::post('status-check', [DeviceController::class, 'checkStatus']);
        Route::post('delete', [DeviceController::class, 'delete']);
        Route::post('details', [DeviceController::class, 'getDeviceDetails']);
        Route::post('instant-show', [DeviceController::class, 'instantShow']);
        Route::post('check-mac-addresses', [DeviceController::class, 'CheckMacAddresses']);
        Route::post('check-mac-addresses-multi', [DeviceController::class, 'CheckMacAddressesMulti']);
        Route::post('check-ack-mac-addresses-multi', [DeviceController::class, 'checkAckAgaisnttMacAddress']);
        Route::post('device-acknowledge', [DeviceController::class, 'deviceAcknowledge']);
        Route::post('change-product', [DeviceController::class, 'changeProduct']);
    });

    // Iot Things Routes
    Route::prefix('things')->group(function () {
        Route::post('store', [ThingController::class, 'store']);
        Route::post('update', [ThingController::class, 'update']);
        Route::post('delete', [ThingController::class, 'delete']);
        Route::post('listing', [ThingController::class, 'listing']);
        Route::post('instant-show', [ThingController::class, 'instantShow']);
    });

    Route::prefix('locations')->group(function () {
        Route::get('listing', [GeolocationController::class, 'listing']);
    });

    Route::prefix('schedules')->group(function () {
        Route::get('listing', [ScheduleController::class, 'listing']);
        Route::get('dalua', [ScheduleController::class, 'dalua']);
        Route::get('public', [ScheduleController::class, 'public']);
        Route::post('store', [ScheduleController::class, 'store']);
        Route::post('store-graph', [ScheduleController::class, 'storeGraph']);
        Route::post('update-name', [ScheduleController::class, 'updateName']);
        Route::post('update', [ScheduleController::class, 'update']);
        Route::post('update-easy', [ScheduleController::class, 'updateEasyModeSchedule']);
        Route::post('delete', [ScheduleController::class, 'delete']);
        Route::post('resend', [ScheduleController::class, 'resend']);
    });

    Route::prefix('share')->group(function () {
        Route::post('device', [ShareController::class, 'device']);
        Route::post('group', [ShareController::class, 'group']);
        Route::post('aquarium', [ShareController::class, 'aquarium']);
        Route::post('remove-aquarium', [ShareController::class, 'removeAquarium']);
        Route::post('get-users', [ShareController::class, 'getUsers']);
    });
    Route::prefix('ota')->group(function () {
        Route::get('get/files', [IotConfigurationFileController::class, 'getOTAFilesForMobiles']);
    });

});


Route::post('iot-configuration-authenticate', [IotConfigurationFileController::class, 'authenticate']);
Route::post('version-log', [IotConfigurationFileController::class, 'versionLog']);
Route::get('iot-updated-file', [IotConfigurationFileController::class, 'getUpdatedFile']);
Route::post('iot-update-log', [IotConfigurationFileController::class, 'updateLog']);
Route::get('iot-get-logs', [IotConfigurationFileController::class, 'getLogs']);

// Route::post('iot-configuration-authenticate-dev-v4', [IotConfigurationFileV4Controller::class, 'authenticate']);
// Route::get('iot-updated-file-dev-v4', [IotConfigurationFileV4Controller::class, 'getUpdatedFile']);
