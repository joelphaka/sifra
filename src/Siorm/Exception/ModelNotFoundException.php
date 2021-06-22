<?php


namespace Sifra\Siorm\Exception;


use Sifra\Http\Exception\HttpNotFoundException;

class ModelNotFoundException extends HttpNotFoundException
{
    public function __construct()
    {
        parent::__construct('Model not found');
    }
}