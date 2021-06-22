<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2019/05/20
 * Time: 19:20
 */

namespace Sifra\Security;


class Hash
{
    public static function generate($length = 32)
    {
        if (version_compare(PHP_VERSION, '7', '>=')) {
            
            return bin2hex(random_bytes($length));
        } else {
            if (function_exists('openssl_random_pseudo_bytes')) {
                return bin2hex(openssl_random_pseudo_bytes($length));

            } else if (function_exists('mcrypt_create_iv')) {
                return mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
            }
        }
        
        throw new \RuntimeException("Could not generate hash.");
    }

    public static function make($length = 32)
    {
        return self::generate($length);
    }

    public static function bcrypt($password, $cost = PASSWORD_BCRYPT_DEFAULT_COST)
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);
    }

    public static function hashPassword($password, $algo = PASSWORD_DEFAULT, $options = null)
    {
        return password_hash($password, $algo, $options);
    }

    public static function verifyPassword ($password, $hash)
    {
       return password_verify($password, $hash);
    }

    public static function verify($hash, $str)
    {
        return hash_equals($hash, $str);
    }
}