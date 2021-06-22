<?php


namespace Sifra\Siorm\Collectors;


use ArrayObject;

abstract class CollectionBase extends ArrayObject
{
    public function first()
    {
        $firstKey = array_key_first($this->getArrayCopy());
        return $firstKey !== null && $this->offsetExists($firstKey) ? $this->offsetGet($firstKey) : null;
    }

    public function last()
    {
        $lastKey = array_key_last($this->getArrayCopy());

        return $lastKey !== null && $this->offsetExists($lastKey) ? $this->offsetGet($lastKey) : null;
    }

    public function all()
    {
        return $this->getArrayCopy();
    }
}