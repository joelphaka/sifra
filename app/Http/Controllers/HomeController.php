<?php
/**
 * @author Joël Phaka
 * Date: 2019/01/20
 * Time: 21:03
 */

namespace App\Http\Controllers;


use App\Models\Country;
use App\Models\City;
use App\Models\Post;
use App\Models\User;
use Sifra\Core\BaseObject;
use Sifra\Core\Env;
use Sifra\Http\Controller;
use Sifra\Http\Request;
use Sifra\Http\Routing\Route;
use Sifra\Security\Hash;
use Sifra\Siorm\DB;
use Sifra\Siorm\Models\Model;
use Sifra\Siorm\QueryBuilder;
use Sifra\Siorm\Validation\Validator;
use Sifra\Templating\View;
use Sifra\Siorm\Util\DbUtils;

class HomeController extends Controller
{
    public function index()
    {
        $this->render('home/index');
    }
}