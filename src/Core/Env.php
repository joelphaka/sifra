<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2019/05/19
 * Time: 23:14
 */

namespace Sifra\Core;


use DirectoryIterator;

final class Env
{
    private $envVars = array();
    private $config = array();

    private static $instance;

    private function __construct()
    {
        $this->envVars = parse_ini_file(baseDir('.env'));

        foreach (glob(baseDir('config') . '/*.php') as $filename) 
        {
            $name = pathinfo($filename, PATHINFO_FILENAME);

            // Important: the required file should return an array
            $this->config[$name] = require_once $filename;
        }
    }

    public function get($key, $defaultValue = null)
    {
        return $this->has($key) ? $this->envVars[$key]: ($defaultValue ?: null);
    }

    public function has($key)
    {
        return isset($this->envVars[$key]);
    }

    public function all()
    {
        return $this->envVars;
    }

    public function config($key)
    {
        return arrayDotKeys($this->config, $key);
    }

    public static function current()
    {
        if (self::$instance == null) {
            self::$instance = new Env();
        }

        if (func_num_args() > 0) {
            return call_user_func_array([self::$instance, 'get'], func_get_args());
        }

        return self::$instance;
    }
}