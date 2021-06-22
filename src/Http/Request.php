<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2018/12/25
 * Time: 11:34
 */

namespace Sifra\Http;


use Sifra\Http\Routing\Route;
use Sifra\Http\Routing\Router;
use Sifra\IO\UploadedFile;
use Sifra\Core\ParameterBag;
use Sifra\Siorm\Collectors\Collection;
use Sifra\Siorm\Validation\ValidationResult;

class Request extends ParameterBag
{
    /**
     * @var
     */
    private $url;

    /**
     * @var
     */
    private $path;

    /**
     * @var string
     */
    private $method;

    /**
     * @var ParameterBag
     */
    private $query;

    /**
     * @var ParameterBag
     */
    private $params;

    /**
     * @var
     */
    private $queryString;

    /**
     * @var
     */
    private $validationResult;

    private $files;


    public function __construct()
    {
        parent::__construct(self::is('get') ? $_GET : $_POST);

        $uriInfo = parse_url($_SERVER['REQUEST_URI']);

        $this->url = url();

        $this->path = $uriInfo['path'];

        $this->method = strtolower(self::is('GET') ? 'GET' : 'POST');

        $this->query = new ParameterBag($_GET);
        
        $this->params = new ParameterBag([]);

        $this->queryString = isset($uriInfo['query']) ? $uriInfo['query'] : '';

        $this->validationResult = new ValidationResult([]);

        $this->files = $this->getUploadedFiles();
    }

    /**
     * @return string
     */
    public function method()
    {
        return $this->method;
    }

    public function isMethod($method)
    {
        return $this->method = strtolower($method);
    }

    public function isPost()
    {
        return $this->method = 'post';
    }

    public function isGet()
    {
        return $this->method = 'get';
    }

    public static function is($method)
    {
        return strtolower($_SERVER['REQUEST_METHOD']) == strtolower($method);
    }

    public static function isAjax()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            return $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
        }

        return false;
    }

    public function path()
    {
        return $this->path;
    }

    public function url()
    {
        return $this->url;
    }

    public function queryString()
    {
        return $this->queryString;
    }

    /**
     * Query string parameters
     * @param null $key
     * @return mixed|\Sifra\Core\ParameterBag
     */
    public function query($key = null)
    {
        if (func_num_args() == 0) {
            return $this->query;
        }

        return $this->query->offsetGet($key);
    }

    /**
     * Route params
     *
     * @param [type] $key
     * @return mixed|\Sifra\Core\ParameterBag
     */
    public function params($key = null)
    {
        if (func_num_args() == 0) {
            return $this->params;
        }

        return $this->params->offsetGet($key);
    }

    public function __addParam($key, $value)
    {
        $this->params->mutable = true;
        $this->params->offsetSet($key, $value);
        $this->params->mutable = false;
    }

    public function __setParams(array $arr)
    {
        $this->params->attributes = parseInputArray($arr);
    }

    public function __setValidation(ValidationResult $validationResult)
    {
        $this->validationResult = $validationResult;
    }

    public function errors()
    {
        return $this->validationResult;
    }

    public function has($key)
    {
        return $this->offsetExists($key);
    }

    public function hasFile($key)
    {
        return isset($this->files[$key]) &&
            isset($this->files[$key]->tmp_name) &&
            is_uploaded_file($this->files[$key]->tmp_name);
    }

    public function input($key)
    {
        return $this->offsetGet($key);
    }

    public function file($key)
    {
        return $this->hasFile($key) ? new UploadedFile($key) : null;
    }

    public function files()
    {
        return $this->files;
    }

    private function getUploadedFiles()
    {
        $uploadedFiles = [];

        foreach ($_FILES as $name => $props) {
            if (is_array($props['name']) && is_array($props['type']) && is_array($props['tmp_name'])) {

                $fileKeys = array_keys($props['name']);

                foreach ($fileKeys as $fileKey) {
                    foreach ($props as $prop => $value) {
                        $uploadedFiles[$name][$fileKey][$prop] = $value[$fileKey];
                    }

                    $uploadedFiles[$name][$fileKey]['extension'] = pathinfo($uploadedFiles[$name][$fileKey]['name'], PATHINFO_EXTENSION);
                    $uploadedFiles[$name][$fileKey]['hasError'] = $uploadedFiles[$name][$fileKey]['error'];

                    $uploadedFiles[$name][$fileKey] = (object)$uploadedFiles[$name][$fileKey];
                }

            } else {
                $props['extension'] = pathinfo($props['name'], PATHINFO_EXTENSION);
                $props['hasError'] = $props['error'];

                $uploadedFiles[$name] = (object)$props;
            }
        }

        return $uploadedFiles;
    }

    public function route()
    {
        return Router::route();
    }
}