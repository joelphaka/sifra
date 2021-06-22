<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2019/01/22
 * Time: 20:21
 */

namespace Sifra\Core;

use Countable;

class ParameterBag extends BaseObject implements Countable
{
    public function __construct($attributes = array(), $mutable = false)
    {
        parent::__construct(parseInputArray($attributes), $mutable);
    }

    public function only(array $arr)
    {
        return arrayOnly($this->attributes, $arr);
    }

    public function except(array $arr)
    {
        return arrayExcept($this->attributes, $arr);
    }

    public function all()
    {
        return $this->attributes;
    }

    /**
     * Count elements of an object
     * @link https://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return count($this->attributes);
    }

    public function offsetSet($key, $value)
    {
        parent::offsetSet($key, is_array($value) ? parseInputArray($value) : parseInput($value));
    }
}