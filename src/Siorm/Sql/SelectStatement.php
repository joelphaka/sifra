<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2019/04/03
 * Time: 11:01 PM
 */

namespace Sifra\Siorm\Sql;


use Sifra\Core\Gettable;
use Sifra\Siorm\Util\DbUtils;
use Sifra\Siorm\Sql\Component\Countable;
use Sifra\Siorm\Sql\Component\Joinable;
use Sifra\Siorm\Sql\Component\Limitable;
use Sifra\Siorm\Sql\Component\Orderable;
use Sifra\Siorm\Collectors\Collection;

class SelectStatement extends FindableStatement implements Gettable
{
    use Joinable, Orderable, Limitable, Countable;

    protected $commandType = Statement::COMMAND_SELECT;

    public function __construct($table, array $columns = array())
    {
        parent::__construct($table);

        $this->columns = DbUtils::cleanColumns($columns);

        $this->command = "SELECT {columns} FROM {$this->table}";

        $this->prevClause = 'from';
    }

    public function distinct(array $columns = array())
    {
        if (!$this->prevClause) {

            $this->columns = DbUtils::cleanColumns($columns);

            $this->command = "SELECT DISTINCT ". DbUtils::stringify($columns) ." FROM {$this->table}";

            $this->prevClause = 'from';
        }

        return $this;
    }

    public function selectOnly(array $columns = array())
    {
        $this->columns = DbUtils::cleanColumns($columns);
    }

    protected function canFind()
    {
        return in_array($this->prevClause , ['from', 'join']);
    }

    public function getCommand()
    {
        $cols = !count($this->columns) ? '*' : DbUtils::stringify($this->columns);

        $this->command = str_replace('{columns}', $cols, parent::getCommand());

        if ($this->hasLimit) {
            return $this->command . $this->getLimitString();
        }

        return $this->command;
    }

    public function get()
    {
        if ($this->isScalar()) {
            return $this->getResultSet()->fetchScalar();
        } else if ($this->hasResultSet()) {
            return $this->getObjectCollection(func_num_args() ? func_get_arg(0) : null);
        }

        return new Collection();
    }

    public function getArrayCollection()
    {
        if ($this->hasResultSet()) {
            return new Collection( $this->getResultSet()->fetchArray() );
        }

        return new Collection(array());
    }

    public function getObjectCollection($class = null)
    {
        return new Collection( $this->getResultSet()->fetchObjects($class) );
    }

    public function getPage($page = 1, $perPage = 5)
    {
        if ($this->isCountable) return $this;

        if (is_numeric($page)) {
            $page = (int)$page;
        }

        if (is_numeric($perPage)) {
            $perPage = (int)$perPage;
        }

        $page = $page > 0 ? $page : 1;
        $perPage = $perPage > 0 ? $perPage : 5;
        $start = $page > 0 ? ($page * $perPage) - $perPage : 0;

        $this->limit($perPage, $start);

    }

}