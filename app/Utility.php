<?php
/**
 * Created by PhpStorm.
 * User: USER
 * Date: 08/07/2017
 * Time: 12:19 PM
 */

namespace App;
use Illuminate\Support\Facades\Log;
use OneSignal;
class Utility
{
    public static function notifyUser($userID, $message = ""){
        // Fetch tokens for user
        $user = User::findOrFail($userID);
        $tokens = Token::where('user_id', $userID)->get();
        foreach ($tokens as $token){
            $name = $user->name;
            OneSignal::sendNotificationToUser("$name just sent you a message", $token->token, $url = null, $data = null, $buttons = null, $schedule = null);
        }
    }

    public static function log($message = "", $severity = "info"){
        Log::info($message);
    }
}