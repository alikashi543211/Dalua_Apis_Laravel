<?php

namespace App\Traits;

use App\Models\NotificationDevice;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait NotificationTrait
{

    use PushNotificationTrait;

    /**
     * @author Fadi Khan
     * @param Array $recievers
     * @param Integer $senderId
     * @param Array $data
     * @return bool
     */
    public function sendNotificationToMultipleUsers($recievers, $data)
    {
        foreach ($recievers as $key => $reciever) {
            $devices = NotificationDevice::whereUserId($reciever->id)->whereNotNull('token')->get();
            foreach ($devices as $key => $device) {
                $this->mobileNotify($reciever, $device, $data);
            }
            $this->storeNotifications($reciever, $data);
        }

        return true;
    }


    /**
     * @author Fadi Khan
     * @param Object $recieverId
     * @param Integer $senderId
     * @param Array $data
     * @return bool
     */
    public function sendNotificationToSingleUser($user, $data)
    {
        if ($user->where('notification_status', ACTIVE)->exists()) {
            $devices = NotificationDevice::whereUserId($user->id)->whereStatus(ACTIVE)->whereNotNull('token')->get();
            foreach ($devices as $key => $device) {
                $this->mobileNotify($device, $data);
            }
        }
        $this->storeNotifications($user, $data);

        return true;
    }
}
