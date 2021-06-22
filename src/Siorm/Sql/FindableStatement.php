<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2019/04/03
 * Time: 09:49 PM
 */

namespace Sifra\Siorm\Sql;


use Sifra\Siorm\Sql\Component\Findable;

abstract class FindableStatement extends Statement implements Findable
{
    const OPERATORS = ['<','>','=', '<=','>=','LIKE','!=', '<>'];

    protected $filterCount = 0;

    protected abstract function canFind();

    protected function find($column, $operator, $value, $clause)
    {
        self::check($column, $operator, $clause);

        $this->command .= " {$clause} {$column} {$operator} ?";

        $this->bindings[] = $value;

        $this->filterCount += 1;

        $this->prevClause = 'where';

        return $this;
    }

    public function where($column, $operator, $value = null)
    { 
        if ($this->canFind() || $this->prevClause == 'where') {
            if (func_num_args() == 2) {
                $value = $operator;
                $operator = '=';
            }
           
            if ($this->prevClause == 'where') {
                return $this->find($column, $operator, $value, 'AND');
            } 

            return $this->find($column, $operator, $value, 'WHERE');
        }
        
        return $this;
    }

    public function andWhere($column, $operator, $value = null)
    {
        if ($this->prevClause == 'where') {
            if (func_num_args() == 2) {
                $value = $operator;
                $operator = '=';
            }

            return $this->find($column, $operator, $value, 'AND');
        }

        return $this;
    }

    public function orWhere($column, $operator, $value = null)
    {
        if ($this->prevClause == 'where') {
            if (func_num_args() == 2) {
                $value = $operator;
                $operator = '=';
            }

            return $this->find($column, $operator, $value, 'OR');
        }

        return $this;
    }
}