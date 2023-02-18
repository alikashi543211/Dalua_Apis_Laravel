<?php

namespace App\Traits;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\ServiceAccount;

trait PushNotificationTrait
{
    private $factory;
    private $database;
    private $messaging;

    public function init()
    {
        $factory = (new Factory)->withServiceAccount(env('FIREBASE_CREDENTIALS'));
        $this->database = $factory->withDatabaseUri(env('FIREBASE_DATABASE_URL'))->createDatabase();
        $this->messaging = $factory->createMessaging();
    }

    public function mobileNotify($device, $data)
    {
        $this->init();
        try {
            $message = CloudMessage::withTarget('token', $device->token)
                ->withNotification(Notification::create($data['title'], $data['message'], url('assets/img/logo.png')))
                ->withData($this->pushNotificationPayload($data));
            return $this->messaging->send($message);
        } catch (NotFound $e) {
            $device->delete();
        } catch (Exception $e) {
            return;
        }
    }

    private function pushNotificationPayload($data)
    {
        $body['title'] = $data['title'];
        $body['body'] = $data['message'];
        return $body;
    }


    public function storeNotifications($user, $data)
    {
        $this->init();
        $this->database->getReference("{$user->username}/notifications/")
            ->push([
                'user_id' => $user->id,
                'title' => $data['title'],
                'description' => $data['message'],
                'is_read' => false,
                'created_at' => Carbon::now()
            ]);
    }
}
