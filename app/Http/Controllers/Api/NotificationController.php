<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Notification\UpdateDeviceTokenRequest;
use App\Models\NotificationDevice;
use Doctrine\DBAL\Query\QueryException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    private $notification;

    public function __construct()
    {
        $this->notification = new NotificationDevice();
    }

    public function updateDeviceToken(UpdateDeviceTokenRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if (!$notification = $this->notification->newQuery()->where('uuid', $inputs['uuid'])->first()) {
                $notification = $this->notification->newInstance();
                $notification->user_id = Auth::id();
            }
            $notification->fill($inputs);
            if ($notification->save()) {
                DB::commit();
                return $this->success(__('notification.tokenUpdated'));
            }
            DB::rollback();
            return $this->error(__('notification.tokenUpdate'), ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }
}
