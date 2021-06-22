<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2019/01/19
 * Time: 18:53
 */

namespace Sifra\Core;


interface Arrayable
{
    const TYPE_FUNC = 1;
    const TYPE_PROP = 2;

    /**
     * TODO
     * @param array $except Properties to exclude from the result.
     * @return array
     */
    public function toArray(array $except = array());
}