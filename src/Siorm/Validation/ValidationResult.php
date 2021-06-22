<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2018/12/25
 * Time: 11:14
 */

namespace Sifra\Siorm\Validation;


use JsonSerializable;
use RuntimeException;
use Sifra\Core\Arrayable;
use Sifra\Core\Arrayify;
use Sifra\Core\Jsonify;
use Sifra\Http\Request;

/**
 * Validation result
 */
class ValidationResult implements Arrayable, JsonSerializable
{
    use Arrayify, Jsonify;

    private $errors;
    protected $arrayableMember = 'errors';
    protected $arrayableType = Arrayable::TYPE_PROP;

    public function __construct(array $errors = array())
    {
        $this->errors = $errors;
    }

    public function has($fieldName)
    {
        return isset($this->errors[$fieldName]);
    }

    public function get($fieldName)
    {
        return $this->has($fieldName) ? array_values($this->errors[$fieldName]) : array();
    }

    public function first($fieldName)
    {
        return $this->has($fieldName) ? $this->get($fieldName)[0] : null;
    }

    public function last($fieldName)
    {
        if (!$this->has($fieldName)) return null;

        $lastError = $this->get($fieldName)[count($this->get($fieldName)) - 1];

        return $lastError;
    }

    public function all()
    {
        return array_map(function ($value) {
            return array_values($value);
        }, $this->errors);
    }

    public function hasErrors()
    {
        return count($this->errors) > 0;
    }

    public function isEmpty()
    {
        return !$this->hasErrors();
    }

    public function __setErrors(array $arr) {
        $this->errors = $arr;
    }
}