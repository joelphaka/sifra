<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2019/04/03
 * Time: 10:32 PM
 */

namespace Sifra\Siorm\Sql\Component;


use InvalidArgumentException;

trait Limitable
{
    protected $hasLimit;
    protected $hasOffset;
    protected $rowLimit;
    protected $rowOffset;

    public function limit($n, $offset = null)
    {
        if (!$this->hasLimit) {

            if (!is_numeric($n)) {
                throw new InvalidArgumentException('$n must be an integer');
            } else if ($n < 0) {
                throw new InvalidArgumentException('$n must be a positive integer');
            }

            $this->rowLimit = (int)$n;
            $this->bindings[] = (int)$n;

            if (is_numeric($offset)) {
                if ($offset >= 0) {
                    $this->rowOffset = (int)$offset;
                    $this->bindings[] = (int)$offset;
                    $this->$hasOffset = true;
                } else {
                    throw new InvalidArgumentException('$offset must be an positive integer');
                }
            }

            $this->hasLimit = true;
            $this->prevClause = 'limit';
        }

        return $this;
    }

    protected function getLimitString()
    {
        if ($this->hasLimit) {
            return (is_int($this->rowOffset) && $this->rowOffset >= 0) ? ' LIMIT ? OFFSET ?' : ' LIMIT ?';
        }

        return '';
    }

    public function hasLimit()
    {
        return $this->hasLimit;
    }    
    
    public function hasOffset()
    {
        return $this->hasOffset;
    }
}