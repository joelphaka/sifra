<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2019/05/23
 * Time: 20:27
 */

namespace Sifra\Http\Exception;


use Sifra\Http\Exception\HttpException;

class HttpBadRequestException extends HttpException
{
    public function __construct($message = "'Bad Request'")
    {
        parent::__construct(400, $message);
    }
}