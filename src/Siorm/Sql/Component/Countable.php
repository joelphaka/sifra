<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2019/04/04
 * Time: 08:40 PM
 */

namespace Sifra\Siorm\Sql\Component;


use InvalidArgumentException;

trait Countable
{
    private function doFunction($fn, $column = null)
    {
        $functions = ['COUNT', 'AVG', 'MIN', 'MAX'];

        if (in_array($this->prevClause, $functions)) {
            return $this;
        }

        $fn = strtoupper($fn);

        if (!in_array($fn, $functions)) {
            throw new InvalidArgumentException('SQL Function must be: COUNT, AVG, MIN, MAX');
        }

        if (!trim($column)) {
            if (in_array($fn, ['AVG', 'MIN', 'MAX'])) {
                throw new InvalidArgumentException("SQL Function: {$fn} requires a column.");
            } else {
                $column = '*';
            }
        }

        $sqlFn = "$fn($column)";
        $pattern = '/^(SELECT\s+)(.+)(\s+FROM\s+.+)$/i';

        $this->command = preg_replace($pattern, "$1{$sqlFn}$3", $this->command);

        $this->prevClause = $fn;

        $this->isCountable = true;

        return $this;
    }

    public function count($column = null)
    {
        return $this->doFunction('COUNT', $column);
    }

    public function avg($column)
    {
        return $this->doFunction('AVG', $column);
    }

    public function min($column)
    {
        return $this->doFunction('MIN', $column);
    }

    public function max($column)
    {
        return $this->doFunction('MAX', $column);
    }
}