<?php


namespace App\Models;


use Sifra\Siorm\Models\Model;

class Post extends Model
{
    protected $hasTimestamps = true;
    protected $table = 'posts';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}