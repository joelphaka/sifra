<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2019/05/19
 * Time: 21:30
 */

namespace Sifra\Http;


use Sifra\Security\Hash;

class Session
{
    public static function get($key)
    {
        return self::has($key) ? $_SESSION[$key] : null;
    }

    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public static function delete($key)
    {
        if(self::has($key)) {
            unset($_SESSION[$key]);
        }
    }
    public static function has($key)
    {
        return isset($_SESSION[$key]);
    }

    public static function flash($key, $value = null)
    {
        if (func_num_args() == 1) {
            if (self::has($key)) {
                $sessionValue = Session::get($key);
                self::delete($key);

                return $sessionValue;
            }
        }

        Session::set($key, $value);

        return null;
    }

    public static function token($key = '_token')
    {
        if (!self::has($key)) {
            self::set($key, Hash::generate());
        }

        return self::get($key);
    }

    public static function csrf()
    {
        return self::token('_csrf');
    }

    public static function start()
    {
        if (session_status() == PHP_SESSION_NONE) {
            ob_start();
            session_start();
        }
    }

    public static function destroy()
    {
        self::start();

        session_unset();
        session_destroy();
    }
}