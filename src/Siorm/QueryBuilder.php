<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2019/02/03
 * Time: 21:15
 */

namespace Sifra\Siorm;


use Sifra\Siorm\Sql\DeleteStatement;
use Sifra\Siorm\Sql\InsertStatement;
use Sifra\Siorm\Sql\SelectStatement;
use Sifra\Siorm\Sql\UpdateStatement;

final class QueryBuilder
{
    private $table;

    private function __construct($table)
    {
        $this->table = $table;
    }

    public static function table($table)
    {
        return new QueryBuilder($table);
    }

    public function insert()
    {
        return new InsertStatement($this->table);
    }

    public function update()
    {
        return new UpdateStatement($this->table);
    }

    public function delete()
    {
        return new DeleteStatement($this->table);
    }

    public function select(...$columns)
    {
        return new SelectStatement($this->table, isset($columns[0]) && is_array($columns[0]) ? $columns[0] : $columns);
    }

    public function selectDistinct(...$columns)
    {
        return (new SelectStatement($this->table))
            ->distinct(isset($columns[0]) && is_array($columns[0]) ? $columns[0] : $columns);
    }
}