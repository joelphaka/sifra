<?php


namespace Sifra\Core;


trait Jsonify
{
    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        if (func_num_args() && is_array(func_get_arg(0))) {
            return $this->toArray(func_get_arg(0));
        }

        return $this->toArray();
    }

    public function toJson(array $except = array())
    {
        return json_encode($this->jsonSerialize($except));
    }
}