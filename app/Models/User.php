<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2019/05/23
 * Time: 22:07
 */

namespace App\Models;


use Sifra\Siorm\Models\Model;
use App\Models\Post;

class User extends Model
{
    protected $hasTimestamps = false;
    protected $table = 'users';
    protected $columns = [
        'first_name',
        'last_name', 
        'email'
    ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}