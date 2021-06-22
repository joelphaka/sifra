<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2019/05/23
 * Time: 20:22
 */

namespace Sifra\Http\Exception;


class HttpNotFoundException extends HttpException
{
    public function __construct($message = "Resource Not Found.")
    {
        parent::__construct(404, $message);
    }
}