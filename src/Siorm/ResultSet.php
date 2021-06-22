<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2019/02/27
 * Time: 21:41
 */

namespace Sifra\Siorm;


use Exception;
use PDO;
use PDOStatement;

class ResultSet
{
    private $statement;
    private $meta;

    public function __construct(PDOStatement $statement, $meta = array())
    {
        $this->statement = $statement;
        $this->meta = $meta;
    }

    public function hasMeta($name)
    {
        return isset($this->meta[$name]);
    }

    public function getMeta($name)
    {
        return $this->hasMeta($name) ? $this->meta[$name] : null;
    }

    public function hasInsertId() {
        return $this->hasMeta('insert_id');
    }

    public function getInsertId()
    {
        return $this->getMeta('insert_id');
    }

    public function getRowCount()
    {
        return $this->statement->rowCount();
    }

    public function fetchObjects($class = null)
    {
        if ($class) {
            if (class_exists($class)) {
                return $this->statement->fetchAll(PDO::FETCH_CLASS, $class);
            } else {
                throw new Exception("Class{ $class } does not exist.");
            }
        } else {
            return $this->statement->fetchAll(PDO::FETCH_CLASS, \Sifra\Core\BaseObject::class);
        }
    }

    public function fetchArray()
    {
        return $this->statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchScalar()
    {
        if ($this->getRowCount()) {
            return $this->statement->fetchAll(PDO::FETCH_COLUMN)[0];
        }

        return 0;
    }

    public function __destruct()
    {
        $this->statement->closeCursor();
    }
}