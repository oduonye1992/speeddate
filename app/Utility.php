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
        $tokens = Token::where('user_id', $userID)->get();
        foreach ($tokens as $token){
            OneSignal::sendNotificationToUser($message, $token->token, $url = null, $data = null, $buttons = null, $schedule = null);
        }
    }

    public static function log($message = "", $severity = "info"){
        Log::info($message);
    }
}