<?php

namespace App\Helper\Auth;

class RegisterHelper
{
    /**
     * @param $request
     * @return mixed
     */
    public static function requestKey($request)
    {
        $key = self::getKey($request->login);

        $request->merge(
            [
                $key => $request->get('login')
            ]
        );

        return $request;
    }

    public static function getKey($login): string
    {
        return filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
    }
}

