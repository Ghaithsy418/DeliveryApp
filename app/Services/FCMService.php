<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FCMService
{
    protected $messaging;
    protected $userRepsitory;

    public function __construct()
    {
        $firebase = (new Factory)->withServiceAccount(base_path(env("FIREBASE_CREDENTIALS")));
        $this->messaging = $firebase->createMessaging();
    }

    public function sendNotification($deviceToken, $title, $body, array $data = [])
    {
        $notification = Notification::create($title, $body);

        $message = CloudMessage::withTarget("token", $deviceToken)->withNotification($notification)->withData($data);
        return $this->messaging->send($message);
    }

    public function notifyUsers(){
        $title = "Welcome Message";
        $body = "Welcome guys to our brand new Shamify App 😁";

        $users = $this->userRepsitory->getAllUsersHasFcmToken();

        foreach($users as $user){
            $this->sendNotification($user->fcm_token, $title, $body,[]);
        }
    }
}
