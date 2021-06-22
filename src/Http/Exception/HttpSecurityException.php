<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2019/05/23
 * Time: 20:27
 */

namespace Sifra\Http\Exception;


use Sifra\Http\Exception\HttpException;

class HttpSecurityException extends HttpException
{
    public function __construct($message = "")
    {
        parent::__construct(401, 'Unauthorized Access.');
    }
}