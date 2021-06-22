<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2019/05/23
 * Time: 20:15
 */

namespace Sifra\Http\Exception;


use RuntimeException;
use Throwable;

class HttpException extends RuntimeException
{
    public function __construct($code = 500, $message = "Internal Server Error.")
    {
        parent::__construct($message, $code, null);
    }
}