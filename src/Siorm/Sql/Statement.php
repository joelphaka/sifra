<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2019/03/01
 * Time: 22:08
 */

namespace Sifra\Siorm\Sql;


use InvalidArgumentException;
use Sifra\Siorm\DB;
use Sifra\Siorm\Util\DbUtils;
/**
 * The base class of all statements
 */
abstract class Statement
{
    const COMMAND_INSERT = 1;
    const COMMAND_UPDATE = 2;
    const COMMAND_DELETE = 3;
    const COMMAND_SELECT = 4;

    protected $table;
    protected $command;
    protected $commandType;
    protected $columns = array();
    protected $bindings = array();
    protected $prevClause;
    protected $isCountable;


    public function __construct($table)
    {
        $this->table = $table;
    }

    public static function check($column, $operator, $clause = null)
    {
        if (is_array($column)) {
            foreach ($column as $c) {
                if (isNullOrEmpty($c)) {
                    throw new InvalidArgumentException(sprintf("Column cannot be empty: %s", $c));
                }
            }
        } else {
            if (isNullOrEmpty($column)) {
                throw new InvalidArgumentException(sprintf('$column cannot be empty'));
            }
        }

        if (!in_array(strtoupper(trim($operator)), FindableStatement::OPERATORS)) {
            throw new InvalidArgumentException(sprintf("Unsupported operator: '%s'", $operator));
        }

        if ($clause !== null) {
            if (!in_array(strtoupper(trim($clause)), array('WHERE', 'AND', 'OR'))) {
                throw new InvalidArgumentException(sprintf("Unknown clause: '%s'", $clause));
            }
        }
    }

    public function getResultSet(DB $db = null)
    {
        $db = $db instanceof DB ? $db : DB::getDefault();

        return $db->executeStatement($this);
    }

    public function execute(DB $db = null)
    {
        return $this->getResultSet($db)->getRowCount();
    }

    public function exec(DB $db = null)
    {
        return $this->execute($db);
    }

    /**
     * @return mixed
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return mixed
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @return mixed
     */
    public function getCommandType()
    {
        return $this->commandType;
    }

    /**
     * @return mixed
     */
    public function getPrevClause()
    {
        return $this->prevClause;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @return array
     */
    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * @return mixed
     */
    public function isCountable()
    {
        return $this->isCountable;
    }

    public function isScalar()
    {
        return $this->isCountable();
    }

    public function hasResultSet()
    {
        return !$this->isCountable() && $this->commandType == self::COMMAND_SELECT;
    }
}