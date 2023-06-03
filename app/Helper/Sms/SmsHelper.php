<?php

namespace App\Helper\Sms;

class SmsHelper
{
    /**
     * @param $customer
     * @param $text
     * @return false|string
     */
    public static function send($customer, $text): bool|string
    {
        $data = 'key=' . urlencode(config('sms.api.key')) . '&destination=' . urlencode(
                $customer
            ) . '&urgent=true' . '&sender=' . urlencode(config('sms.api.sender')) . '&content=' . urlencode($text);

        $url = config('sms.api.url') . "?" . $data;

        return file_get_contents($url);
    }

}
