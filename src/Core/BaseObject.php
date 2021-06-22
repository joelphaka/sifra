<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2018/12/27
 * Time: 21:39
 */

namespace Sifra\Core;


use ArrayAccess;
use Exception;
use JsonSerializable;
use Sifra\Core\Jsonify;

class BaseObject implements ArrayAccess, Arrayable, JsonSerializable
{
    use Arrayify, Jsonify;

    protected $attributes = array();
    protected $mutable = true;

    public function __construct($attributes = array(), $mutable = true)
    {
        if (count($this->attributes)) return;

        if (func_num_args() == 0) {
            $this->attributes = $attributes;
            $this->mutable = (boolean)$mutable;
        } else if (func_num_args() == 1) {
            if (is_bool(func_get_arg(0))) {
                $this->attributes = array();
                $this->mutable = $attributes;
            } else if (is_array(func_get_arg(0))) {
                $this->attributes = $attributes;
                $this->mutable = (boolean)$mutable;
            } else {
                $this->attributes = array();
                $this->mutable =  true;
            }
        } else if (func_num_args() > 1) {
            if (!is_array($attributes)) {
                throw new Exception('attributes must be an array');
            }

            $this->attributes = $attributes;
            $this->mutable = (boolean)$mutable;
        }
    }

    protected function getAttribute($key) 
    {
        return $this->offsetGet($key);
    }

    protected function setAttribute($key, $value) 
    {
        $this->offsetSet($key, $value);
    }

    public function hasAttribute($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $key <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $key <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($key)
    {
        return $this->offsetExists($key) ? $this->attributes[$key] : null;
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $key <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     * @throws Exception If the $key is null or an empty string.
     */
    public function offsetSet($key, $value)
    {
        if ($this->mutable) {
            if (is_null($key) || empty($key)) {
                throw new Exception('$key cannot be null or string.');
            }

            $this->attributes[$key] = $value;
        }
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $key <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($key)
    {
        unset($this->attributes[$key]);
    }

    public function __get($key)
    {
        return $this->offsetGet($key);
    }

    public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    public function __unset($key)
    {
        unset($this->attributes[$key]);
    }
}