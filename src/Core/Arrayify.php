<?php


namespace Sifra\Core;


use ArrayObject;
use \RuntimeException;
use Sifra\Core\Arrayable;

trait Arrayify
{
    /**
     * Returns the array representation
     * @param array $except Properties to exclude from the result.
     * @return array
     */
    public function toArray(array $except = array())
    {
        if (property_exists($this, 'arrayableType') && $this->arrayableType == Arrayable::TYPE_PROP) {
            if (property_exists($this, 'arrayableMember') && !property_exists($this, $this->arrayableMember)) {
                throw new RuntimeException("arrayableMember property { $this->arrayableMember } does not exist.");
            }
        } else if (property_exists($this, 'arrayableType') && $this->arrayableType == Arrayable::TYPE_FUNC) {
            if (property_exists($this, 'arrayableMember') && !method_exists($this, $this->arrayableMember)) {
                throw new RuntimeException("arrayableMember function { $this->arrayableMember } does not exist.");
            }
        } else {
            if (!property_exists($this, 'attributes')) {
                throw new RuntimeException("arrayableMember property { attributes } does not exist.");
            }
        }

        return toArray($this->getMemberValue(), $except);
    }

    private function getMemberValue()
    {
        if ($this->arrayableType == Arrayable::TYPE_PROP) {
            if (property_exists($this, $this->arrayableMember)) {
                return $this->{$this->arrayableMember};
            }
        } else if ($this->arrayableType = Arrayable::TYPE_FUNC) {
            if (method_exists($this, $this->arrayableMember)) {
                return call_user_func_array([$this, $this->arrayableMember], array());
            }

        }

        return $this->{'attributes'};
    }

}