<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2019/04/03
 * Time: 10:39 PM
 */

namespace Sifra\Siorm\Sql\Component;


use InvalidArgumentException;
use Sifra\Siorm\Sql\FindableStatement;
use Sifra\Siorm\Sql\Statement;

trait Orderable
{
    private function order($direction, $column)
    {
        if (in_array($this->prevClause, ['from','join','where'])) {

            Statement::check($column, '=');

            if (!in_array(strtoupper($direction), ['ASC', 'DESC'])) {
                $errMsg = sprintf("Invalid order direction: '%s'. ASC or DESC expected", $direction);
                throw new InvalidArgumentException($errMsg);
            }

            $this->command .= " ORDER BY {$column} {$direction}";
            $this->prevClause = 'order';
        }

        return $this;
    }

    public function orderAsc($column)
    {
        return $this->order('ASC', $column);
    }

    public function orderDesc($column)
    {
        return $this->order('DESC', $column);
    }
}