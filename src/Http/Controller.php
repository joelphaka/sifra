<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2019/01/19
 * Time: 20:44
 */

namespace Sifra\Http;


use Sifra\Siorm\Validation\Validator;
use Sifra\Templating\View;

abstract class Controller
{
    protected function validate(Request $request, $rules, $customMessages = array())
    {
        return (new Validator())->validate($request, $rules, $customMessages);
    }

    protected function render($path, array $data = array())
    {
        echo View::createContent($path, $data);
    }
}