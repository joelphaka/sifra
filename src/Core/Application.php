<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2019/06/03
 * Time: 20:16
 */

namespace Sifra\Core;


class Application extends BaseObject
{
    public function __construct()
    {
        parent::__construct(config('app'), true);
    }

    public final function run()
    {
        //TODO
    }

    public function onException($e)
    {
        return null;
    }

    public function onError($e)
    {
        return $this->onException($e);
    }
}