<?php


namespace Sifra\Siorm\Collectors;


use Sifra\Core\Arrayable;
use Sifra\Core\Arrayify;
use JsonSerializable;
use Sifra\Core\Jsonify;
use Closure;

class Collection extends CollectionBase implements Arrayable, JsonSerializable
{
    use Arrayify, Jsonify;

    protected $arrayableType = Arrayable::TYPE_FUNC;
    protected $arrayableMember = 'getArrayCopy';
    public $transformer;

    public function map(Closure $closure)
    {
       return new Collection(array_map($closure, $this->getArrayCopy()));
    }

    public function filter(Closure $closure)
    {
       return new Collection(array_filter($this->getArrayCopy(), $closure));
    }

    public function merge(Collection $collection)
    {
        return new Collection(array_merge($this->getArrayCopy(), $collection->getArrayCopy()));
    }

    public function reverse()
    {
        return new Collection(array_reverse($this->getArrayCopy()));
    }

    public function transformer($transformer)
    {
        if (!(class_exists($transformer) || is_callable($transformer))) {
            throw new \Exception('$transformer must be a class or a function');
        }

        $this->transformer = $transformer;
    }

    public function offsetGet($index)
    {
        if ($this->transformer) {
            if (class_exists($this->transformer)) {
                $class = $this->transformer;
                return new $class(parent::offsetGet($index));

            } else if (is_callable($this->transformer)) {
                return call_user_func_array($this->transformer, [ parent::offsetGet($index) ]);
            }
        }

        return parent::offsetGet($index);
    }

    public static function make($arr)
    {
        return new Collection($arr);
    }
}