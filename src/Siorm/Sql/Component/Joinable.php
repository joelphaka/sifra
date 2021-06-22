<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2019/04/03
 * Time: 10:44 PM
 */

namespace Sifra\Siorm\Sql\Component;


use InvalidArgumentException;
use Sifra\Siorm\Sql\FindableStatement;
use Sifra\Siorm\Sql\Statement;

trait Joinable
{
    /**
     * @param $table
     * @param $type
     * @return $this
     */
    private function join($table, $type)
    {
        if ($this->prevClause == 'from') {

            if (!trim($table)) {
                throw new InvalidArgumentException('$table cannot be empty.');
            }

            if (!in_array(strtoupper($type), array('LEFT', 'RIGHT', 'INNER'))) {
                throw new InvalidArgumentException(sprintf("Unknown join: '%s'. LEFT, INNER or RIGHT expected", $type));
            }

            $this->command .= sprintf(' %s JOIN %s', strtoupper($type), $table);

            $this->prevClause = 'join';
        }

        return $this;
    }

    public function leftJoin($table)
    {
        return $this->join($table, 'LEFT');
    }

    public function innerJoin($table)
    {
        return $this->join($table, 'INNER');
    }

    public function rightJoin($table)
    {
        return $this->join($table, 'RIGHT');
    }

    public function on($column, $otherColumn)
    {
        if ($this->prevClause == 'join') {
            Statement::check([$column, $otherColumn], '=');
            $this->command .= sprintf(' ON %s = %s',
                trim($column),
                trim($otherColumn)
            );

            $this->prevClause = 'join';
        }

        return $this;
    }

    public function onEquals($column, $otherColumn)
    {
        return $this->on($column, $otherColumn);
    }
}