<?php


namespace Sifra\Siorm;


use http\Exception\InvalidArgumentException;
use Sifra\Core\Gettable;
use Sifra\Siorm\Models\Model;
use Sifra\Siorm\Sql\SelectStatement;
use Sifra\Siorm\Sql\Component\Findable;

class Queryable implements Findable, Gettable
{
    private $class;
    private $selectStatement;
    private $fillerCount = 0;

    public function __construct($class)
    {
        if (!Model::isModel($class)) {
            throw new InvalidArgumentException('class must be a Sifra\Siorm\Models\Model');
        }

        $this->class = $class;
        $this->selectStatement = new SelectStatement($class::meta()->table());
    }

    public function where($column, $operator, $value = null)
    {
        $this->selectStatement->where($column, $operator, $value);

        return $this;
    }

    public function andWhere($column, $operator, $value = null)
    {
        $this->selectStatement->andWhere($column, $operator, $value);

        return $this;
    }

    public function orWhere($column, $operator, $value = null)
    {

        $this->selectStatement->orWhere($column, $operator, $value);

        return $this;
    }

    public function orderAsc($column)
    {
         $this->selectStatement->orderAsc($column);

         return $this;
    }

    public function orderDesc($column)
    {
        $this->selectStatement->orderDesc($column);

        return $this;
    }

    public function limit($n, $offset = null)
    {
        $this->selectStatement->limit($n, $offset);

        return $this;
    }

    public function select(array $columns = array())
    {
        $this->selectStatement->selectOnly($columns);

        return $this;
    }

    public function count($column = null)
    {
        $this->selectStatement->selectOnly(array());

        $this->selectStatement->count($column);

        return $this;
    }

    public function min($column = null)
    {
        $this->selectStatement->selectOnly(array());

        $this->selectStatement->min($column);

        return $this;
    }

    public function max($column = null)
    {
        $this->selectStatement->selectOnly(array());

        $this->selectStatement->max($column);

        return $this;
    }

    public function avg($column = null)
    {
        $this->selectStatement->selectOnly(array());

        $this->selectStatement->avg($column);

        return $this;
    }

    public function get()
    {
        return $this->selectStatement->get($this->class);
    }

    public function first() 
    {
        if (!$this->selectStatement->isScalar() && !$this->selectStatement->hasLimit()) {
            
            $this->limit(1);
            
            return $this->get()->first();
        }

        return null;
    }
}