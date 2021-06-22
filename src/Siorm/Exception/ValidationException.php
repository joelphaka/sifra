<?php

namespace Sifra\Siorm\Exception;


use Sifra\Http\Exception\HttpException;

class ValidationException extends HttpException
{
    public function __construct()
    {
        parent::__construct(500, "Validation failed");
    }
}