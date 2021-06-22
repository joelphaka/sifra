<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2019/05/19
 * Time: 21:33
 */

namespace Sifra\Http;


class Cookie
{
    public static function get($key)
    {
        return self::has($key) ? $_COOKIE[$key] : null;
    }

    public static function set($key, $value, $expiry)
    {
        if (setcookie($key, $value, time() + $expiry)) {
            return true;
        }

        return false;
    }

    public static function has($key)
    {
        return isset($_COOKIE[$key]);
    }

    public static function delete($key)
    {
        if(self::has($key)) {
            unset($_COOKIE[$key]);
        }
    }
}