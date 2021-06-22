<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2019/05/20
 * Time: 19:43
 */

namespace Sifra\Security;


use Sifra\Http\Session;

class Auth
{
    private static $user;

    public static function user()
    {
        if (Session::has('user') && !self::$user) {
            self::$user = '';
        }

        return self::$user;
    }

    public static function isAuth()
    {
        return Session::has('user') && self::$user;
    }

    public static function login(array $credentials, $remember = false)
    {
        //TODO
    }
}