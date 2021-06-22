<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2019/01/19
 * Time: 19:53
 */

namespace Sifra\Http\Routing;


use Exception;
use InvalidArgumentException;
use RuntimeException;
use Sifra\Http\Exception\HttpNotFoundException;
use Sifra\Http\Request;
use Sifra\Http\Session;

final class Router
{
    private static $routes = array();
    private static $currentRoute;

    public static function get($path, $action)
    {
        return self::set('get', $path, $action);
    }

    public static function post($path, $action)
    {
        return self::set('post', $path, $action);
    }

    private static function set($method, $path, $action)
    {
        $method = trim($method);
        $path = trim($path);

        if (empty($method)) {
            throw new InvalidArgumentException('method cannot be empty or null.');
        }

        if (empty($path)) {
            throw new InvalidArgumentException('path cannot be empty or null.');
        }

        if (!(is_callable($action) || is_string($action) || is_array($action))) {
            throw new InvalidArgumentException('action must be a closure, an array or a string.');
        }

        if (is_string($action)) {
            if (empty($action)) {
                throw new InvalidArgumentException('action cannot be empty or null.');
            }

            if (!strpos($action, '@')) {
                throw new InvalidArgumentException('Invalid action string format.');
            }

            $action_info = explode('@', $action);


            if (count($action_info) < 2) {
                throw new InvalidArgumentException("invalid action.");
            }

            $action = array();
            $action[0] = $action_info[0];
            $action[1] = $action_info[1];

        } else if (is_array($action)) {

            if (count($action)) {
                if (count($action) === 1) {

                    if (is_string($action[0])) {
                        $action_info = explode('@', $action[0]);

                        $action[0] = $action_info[0];
                        $action[1] = $action_info[1];
                    }
                } else if (count($action) >= 1) {
                    if (!(is_string($action[0]) && is_string($action[1]))) {
                        throw new InvalidArgumentException("invalid action.");
                    }
                }
            } else {
                throw new InvalidArgumentException("invalid action.");
            }
        }

        return self::$routes[] = array(
            'method' => $method,
            'path' => strlen($path) > 1 ? rtrim($path, '/') : $path,
            'action' => is_callable($action) ? $action : [ trim($action[0]), trim($action[1]) ]
        );
    }

    private static function createRoute($method, $path)
    {
        $method = trim(strtolower($method));
        $path = parse_url(trim($path))['path'];

        if (strlen($path) > 1) {
            $path = rtrim($path, '/');
        }

        // Only get the routes for the current request method (GET | POST)
        $fltRoutes = array_filter(self::$routes, function ($r) use ($method) {
            return $r['method'] == $method;
        });


        // The request is passed as the first argument to the action method
        $request = new Request();
        // Will store data about the matched route
        $routeData = null;

        foreach ($fltRoutes as $r) {

            // Check if the incoming uri matches any route
            if ($r['path'] == $path) {
                $routeData = $r;
                $routeData['path'] = $path;
                // Store the request to the array of arguments that will be passed the acton method
                $routeData['params'] = array($request);
                // Found our match, So break !
                break;
            }

            // If the route has parameters, further processing is required to find the matching round

            $tmpPath = $r['path'];

            // Optional parameters
            $pathPattern = preg_replace('/\/\:[^\/\?]+\?/', '(?:\/([^\/]*))?', $tmpPath); //-
            // Required parameters
            $pathPattern = preg_replace('/\/\:[^\/\(\\\]+/', '\/([^\/]+)', $pathPattern); //-

            $pathPattern = preg_replace('/\)\//', ')\/', $pathPattern);
            $pathPattern = preg_replace('/(?:\/)+/', '\/', $pathPattern);

            $pathPattern = str_replace('\\\/', '\\/', $pathPattern);

            $pathPattern = '/^' . $pathPattern . '$/i';

            if (preg_match($pathPattern, $path, $paramValues)) {

                $routeData = $r;
                $routeData['path'] = $path;
                unset($paramValues[0]);
                $paramValues = array_values($paramValues);
                $routeParams = array();

                if (preg_match_all('/\:([^\/\?]+)/', $tmpPath, $paramNames)) {

                    foreach ($paramValues as $index => $value) {
                        $paramName = $paramNames[1][$index];
                        $routeParams[$paramName] = $value;
                    }

                }

                $request->__setParams($routeParams);

                $routeData['params'] = array_merge([ $request ], $routeParams);
            }
        }

        if ($routeData) {
            $rd = (object) $routeData;

            return new Route($method, $path, $rd->action , $rd->params);
        }

        return null;
    }

    public static function create($method, $path)
    {
        self::$currentRoute = self::createRoute($method, $path);

        return self::$currentRoute;
    }

    public static function createOrFail($method, $path)
    {
        if (self::create($method, $path) instanceof Route) {
            return self::$currentRoute;
        }

        throw new HttpNotFoundException("Route{ " . parse_url($path)['path']." } Not Found.");
    }

    public static function run($method, $path, $successHandler, $errorHandler = null)
    {
        try {
            self::createOrFail($method, $path);
            
            if (!(is_callable($successHandler) || is_array($successHandler))) {
                throw new RuntimeException('Handler must be a Closure or array[object, method]');
            }

            return call_user_func_array($successHandler, array(self::$currentRoute));

        } catch (Exception $ex) {
            if (!(is_callable($successHandler) || is_array($successHandler))) {
                throw new RuntimeException('Handler must be a Closure or array[object, method]');
            }

            return call_user_func_array($errorHandler, array($ex));
        }
    }

    public static function execute($method, $path, $errorHandler = null)
    {
        return self::run($method, $path, function (Route $route) {

            $route->routeTo();

        }, $errorHandler);
    }

    public static function beginRouting(\Closure $errorHandler = null)
    {
        return self::init($errorHandler);
    }

    public static function init(\Closure $errorHandler = null)
    {
        try {
            self::createOrFail($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

            // Validate the CSRF token before routing to action
            if (request()->isPost() && !request()->isAjax()) {
                if (request()->has('_csrf') && request()->input('_csrf') != Session::csrf()) {
                    raise(400, "Invalid CSRF Token");
                }
            }

            return self::route()->routeTo();

        } catch (\Exception $e) {
            return call_user_func_array($errorHandler, [$e]);
        }
    }

    public static function route()
    {
        return self::$currentRoute;
    }

    /**
     * @return array
     */
    public function routes()
    {
        return $this->routes;
    }
}