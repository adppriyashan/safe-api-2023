<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait SendSMS
{
    protected static $url = 'https://www.textit.biz/sendmsg';

    public static function sendNow($text,$to)
    {

        return Http::get(self::$url, [
            'id' => env('SMS_GATEWAY_USERNAME'),
            'pw' => env('SMS_GATEWAY_PASSWORD'),
            'text' => urlencode($text),
            'to' => $to,
        ]);
    }
}
