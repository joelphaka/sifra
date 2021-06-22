<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2019/01/19
 * Time: 19:53
 */

namespace Sifra\Http\Routing;


use Closure;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use Sifra\Core\BaseObject;
use Sifra\Core\ParameterBag;
use Sifra\Http\BasicController;
use Sifra\Http\Controller;
use Sifra\Http\Request;

final class Route
{
    private $method;
    private $path;
    private $controller;
    private $action;
    private $params;
    private $isClosure;

    public function __construct($method, $path, $action, $params = array())
    {
        $this->method = $method;
        $this->path = $path;

        if (func_num_args() <= 4) {
            if (is_array($action)) {
                if (count($action) < 2) {
                    throw new Exception('Invalid action.');
                }

                $this->controller = $action[0];
                $this->action = $action[1];
            } else if (is_callable($action)) {
                $this->controller = BasicController::class;
                $this->action = $action;

                $this->isClosure = true;
            } else {
                throw new InvalidArgumentException('action must be an array or a closure.');
            }

            $this->params = $params;

        } else if (func_num_args() > 4) {
            $this->controller = $action;
            $this->action = func_get_arg(3);
            $this->params = func_get_arg(4);
        }
    }

    public function __invoke()
    {
        return $this->routeTo();
    }

    public function routeTo()
    {
        if (!in_array(Controller::class, class_parents($this->controller))) {
            throw new RuntimeException("Class{ {$this->controller} } is not a Controller.");
        }


        $obj = new $this->controller;

        if ($this->isClosure) {
            $closure = Closure::bind($this->action, $obj, $obj);

            return call_user_func_array($closure, $this->params);
        }

        if (!method_exists($obj, $this->action)) {
            throw new RuntimeException("Method { {$this->action} } Not found in Class{ {$this->controller} }.");
        }

        return call_user_func_array([$obj, $this->action], $this->params);
    }

    /**
     * @return mixed
     */
    public function method()
    {
        return $this->method;
    }

    /**
     * @return mixed
     */
    public function path()
    {
        return $this->path;
    }

    /**
     * @return BaseObject
     * @throws Exception
     */
    public function params()
    {
        return new ParameterBag($this->params, false);
    }

    public function request()
    {
        return is_a($this->params[0], Request::class) ? $this->params[0] : new Request();
    }

    public static function __callStatic($name, $arguments)
    {
        if (in_array($name, ['get', 'post'], true)) {
            $func = "\Sifra\Http\Routing\Router::{$name}";

            call_user_func_array($func, $arguments);
        }
    }
}