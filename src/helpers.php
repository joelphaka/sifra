<?php
/**
 * Sifra
 * @author Joël Phaka
 */

use Sifra\Core\BaseObject;
use Sifra\Core\Env;
use Sifra\Http\Cookie;
use Sifra\Http\Exception\HttpException;
use Sifra\Http\Exception\HttpNotFoundException;
use Sifra\Http\Exception\HttpSecurityException;
use Sifra\Http\Routing\Router;
use Sifra\Http\Session;
use Sifra\Siorm\Validation\Validator;
use Sifra\Core\Arrayable;
use Sifra\Core\ParameterBag;
use Sifra\Http\Exception\HttpBadRequestException;

if (!function_exists('sessionGet')) {

    function sessionGet($key) {
        return Session::get($key);
    }
}

if (!function_exists('sessionSet')) {

    function sessionSet($key, $value) {
        Session::set($key, $value);
    }
}

if (!function_exists('sessionDelete')) {

    function sessionDelete($key) {
        Session::delete($key);
    }
}

if (!function_exists('sessionHas')) {

    function sessionHas($key) {
        return Session::has($key);
    }
}

if (!function_exists('sessionFlash')) {

    function sessionFlash($key, $value = null) {
        return Session::flash($key, $value);
    }
}

if (!function_exists('sessionStart')) {

    function sessionStart()
    {
        Session::start();
    }
}

if (!function_exists('sessionDestroy')) {

    function sessionDestroy()
    {
        Session::destroy();
    }
}

if (!function_exists('token')) {

    function token($key = '_token')
    {
        return Session::token($key);
    }
}

if (!function_exists('csrf')) {

    function csrf()
    {
        return Session::csrf();
    }
}

if (!function_exists('cookieGet')) {

    function cookieGet($key)
    {
        return Cookie::get($key);
    }
}

if (!function_exists('cookieSet')) {

    function cookieSet($key, $value, $expiry)
    {
        Cookie::set($key, $value, $expiry);
    }
}

if (!function_exists('toArray')) {
    
    function toArray($data, array $except = array()) {

        if (is_object($data)) {
            if ($data instanceof Arrayable) {
                return toArray($data->toArray($except), $except);
            } else if ($data instanceof StdClass) {
                return toArray((array)$data, $except);
            }
        } else if (is_array($data)) {

            unset($data['arrayableType']);
            unset($data['arrayableMember']);

            foreach ($data as $key => $value) {
                // --
                if (in_array($key, $except, true)) {
                    unset($data[$key]);
                    continue;
                }

                if (is_object($value)) {
                    if ($value instanceof Arrayable) {
                        $data[$key] = toArray($value->toArray($except), $except);
                    } else if ($value instanceof StdClass) {
                        $data[$key] = toArray((array)$value, $except);
                    }

                } else if (is_array($value)) {
                    $data[$key] = toArray($value, $except);
                } else {
                    $data[$key] = $value;
                }
            }

            return $data;
        }

        throw new \InvalidArgumentException('array, StdClass or \Sifra\Core\BaseObject instance expected.');
    }
}

if (!function_exists('arrayExcept')) {

    function arrayExcept(array $arr, array $except = array()) {

        if (!count($except)) return $arr;

        return array_filter($arr, function ($key) use ($except) {
            echo '<br>' . $key;
            return !in_array($key, $except);

        }, ARRAY_FILTER_USE_KEY);
    }
}

if (!function_exists('arrayOnly')) {

    function arrayOnly(array $arr, array $only = array()) {

        if (!count($only)) return $arr;

        return array_filter($arr, function ($key) use ($only) {

            return in_array($key, $only);

        }, ARRAY_FILTER_USE_KEY);
    }
}

if (!function_exists('sanitizePath')) {

    function sanitizePath($path)
    {
        return preg_replace('/\\//', DIRECTORY_SEPARATOR, trim($path, '/\/'));
    }
}

if (!function_exists('baseDir')) {

    function baseDir($arg = null)
    {
        $path = BASE_DIR;

        if ($arg) {
            $path = $path . DIRECTORY_SEPARATOR . sanitizePath($arg);
        }

        return $path;
    }
}

if (!function_exists('appDir')) {

    function appDir($arg = null)
    {
        return baseDir(joinPaths('app', $arg ?: ''));
    }
}

if (!function_exists('resource')) {

    function resource($path)
    {
        return rtrim( settings('paths.resources'), DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR . sanitizePath($path);
    }
}


if (!function_exists('asset')) {
    function asset($path)
    {
        return url( 'assets' . '/' .  trim($path, "\\/"));
    }
}

if (!function_exists('paths')) {

    function path($key)
    {
        $path = settings('paths.'. $key);

        if (is_dir($path) || file_exists($path)) {
            return sanitizePath($path);
        }

        throw new HttpNotFoundException("path { $path } not found");
    }
}

if (!function_exists('joinPaths')) {

    function joinPaths(...$paths) {
        return implode(DIRECTORY_SEPARATOR, $paths);
    }
}

if (!function_exists('url')) {

    function url($path = null, $queryData = array(), $appendQuery = true)
    {
        $isHttps = isset($_SERVER['HTTPS']) && stripos($_SERVER['SERVER_PROTOCOL'], 'https');

        $protocol = $isHttps ? 'https' : 'http';
        $remoteHost = $_SERVER['HTTP_HOST'];

        $url = "{$protocol}://{$remoteHost}";
        $currentUrl = $url . $_SERVER['REQUEST_URI'];

        $path = trim( trim($path), '\\/' );


        if (!$path && !count($queryData)) {
            return $currentUrl;
        }

        $path = !$path ? '/' : $path;

        if (!$path) {
            return $currentUrl;
        }

        $url .= $path == '/' ? $path : '/' . $path;

        $queryString = function() use ($queryData, $appendQuery) {

            foreach ($queryData as $key => $value) {
                if (filter_var($value, FILTER_VALIDATE_URL)) {
                    $queryData[$key] = urlencode($value);
                }
            }

            return http_build_query($appendQuery ? array_merge($_GET, $queryData) : $queryData);
        };

        return $url = !count($queryData) ? $url : $url . '?' . $queryString();
    }
}

if (!function_exists('request')) {

    /**
     * @return \Sifra\Http\Request
     */
    function request()
    {
        if (!Router::route()) {
            throw new RuntimeException('No Route');
        }

        if (func_num_args() && func_get_arg(0)) {
            return Router::route()->request()->input(func_get_arg(0));
        }

        return Router::route()->request();
    }
}

if (!function_exists('raise')) {

    function raise($statusCode = 500, $message = null)
    {
        $ex = null;

        if ($statusCode == 500) {
            $ex = new HttpException(500, $message ?: 'Internal Server Error');
        } else if ($statusCode == 404) {
            $ex = new HttpNotFoundException($message ?: 'Resource Not Found');
        } elseif ($statusCode == 401) {
            $ex = new HttpSecurityException($message ?: 'Unauthorized Access.');
        } elseif ($statusCode == 403) {
            $ex = new HttpException(403, $message ?: 'Forbidden');
        } elseif ($statusCode == 400) {
            $ex = new HttpBadRequestException($message ?: 'Bad Request');
        } else {
            $ex = new HttpException($statusCode, $message);
        }

        http_response_code($statusCode);

        throw $ex;
    }
}

if (!function_exists('raiseIf')) {

    function raiseIf($condition, $statusCode = 500, $message = null) {
        if ($condition) {
            raise($statusCode, $message);
        }
    }
}

if (!function_exists('httpServerError')) {

    function httpServerError($message = null)
    {
        $message = is_string($message) ? $message : 'Internal Server Error';

        raise(500, $message);
    }
}

if (!function_exists('httpNotFound')) {
    
    function httpNotFound($message = null)
    {
        $message = is_string($message) ? $message : 'Resource Not Found';

        raise(404, $message);
    }
}

if (!function_exists('httpForbidden')) {
    
    function httpForbidden($message = null)
    {
        $message = is_string($message) ? $message : 'Forbidden';

        raise(403, $message);
    }
}

if (!function_exists('httpUnauthorized')) {
    
    function httpUnauthorized($message = null)
    {
        $message = is_string($message) ? $message : 'Unauthorized Access.';

        raise(401, $message);
    }
}

if (!function_exists('arrayDotKeys')) {

    /**
     *  Array access using dot notation
     * For example. Given an array:
     *  $arr = [
     *      'house' => [
     *          'rooms' => 3, 
     *           'owner' => ['name' => 'J Phaka', 'lang' => 'FR']
     *       ]
     *  ]
     * 
     * To access owner's name
     * 
     * $name = arrayDotKeys($arr, 'house.owner.name');
     */

    function arrayDotKeys(array $arr, $key)
    {
        $keys = explode('.', $key);
        $currItem = null;

        foreach ($keys as $key) {

            if (!$currItem) {
                if (!isset($arr[$key])) return null;

                $currItem = $arr[$key];
            } else {
                if (!isset($currItem[$key])) return null;

                $currItem =  $currItem[$key];
            }
        }

        return $currItem;
    }
}

if (!function_exists('arrayIsAssoc')) {

    function arrayIsAssoc(array $arr)
    {
        $keys = array_keys($arr);

        return $keys == array_values($keys);
    }
}

if (!function_exists('env')) {

    function env($key, $defaultValue = null) {
        return Env::current()->has($key) ? Env::current($key, $defaultValue) : null;
    }
}

if (!function_exists('config')) {

    function config($key)
    {
        return Env::current()->config($key);
    }
}

if (!function_exists('settings')) {

    function settings($key)
    {
        return config("settings.{$key}");
    }
}

if (!function_exists('pipedStringToArray')) {

    function pipedStringToArray($str)
    {
        $pattern = '/([^\|\:]+(?:\:[^\|\:]+)?)/';

        if (preg_match_all($pattern, $str, $matches) > 0) {
            $captures = $matches[1];
            $arr = array();

            foreach ($captures as $capture) {
                $data = explode(':',$capture);

                if (count($data) == 2) {
                    $arr[$data[0]] = $data[1];
                } else {
                    $arr[$data[0]] = true;
                }
            }

            return $arr;
        }

        return array();
    }
}

if (!function_exists('isNullOrEmpty')) {

    function isNullOrEmpty($var) {

        if (is_string($var)) {
            return empty(trim($var));
        } else {
            return $var === null;
        }

    }
}

if (!function_exists('parseInput')) {

    function parseInput($input)
    {
        if (!(is_object($input) || is_array($input))) {
            $input = htmlspecialchars(trim($input));
        }

        if (is_float($input)) {
            $input = filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT);

        } else if (is_int($input)) {
            $input = filter_var($input, FILTER_SANITIZE_NUMBER_INT);

        } else if (filter_var($url = $input, FILTER_VALIDATE_URL)) {
            $input = urldecode($url);

        } else if (is_string($input)) {
            $input = filter_var($input, FILTER_SANITIZE_STRING);
        }

        return $input ?: null;
    }
}

if (!function_exists('parseInputArray')) {

    function parseInputArray(array $data)
    {
        $newData = array();

        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $newData[$k] = parseInputArray($v);
            } else {
                $newData[$k] = parseInput($v);
            }
        }

        return $newData;
    }
}

if (!function_exists('validator')) {
    function validator()
    {
        return new Validator();
    }
}

if (!function_exists('errors')) {
    function errors()
    {
        return request()->errors();
    }
}

if (!function_exists('view')) {

    function view($path, array $viewData = array())
    {
        // TODO
        // See Sifra\Templating\View
    }
}
