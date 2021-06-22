<?php


namespace Sifra\Siorm\Sql\Component;


interface Findable
{
    public function where($column, $operator, $value = null);

    public function andWhere($column, $operator, $value = null);

    public function orWhere($column, $operator, $value = null);
}