<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2019/03/01
 * Time: 22:43
 */

namespace Sifra\Siorm\Sql;


use InvalidArgumentException;
use Sifra\Siorm\Util\DbUtils;

class InsertStatement extends Statement
{
    protected $commandType = self::COMMAND_INSERT;

    public function values(array $bindings)
    {
        if ($this->prevClause) {
            return $this;
        }

        if (!count($bindings)) {
            throw new InvalidArgumentException('$bindings cannot be an empty array.');
        }

        $this->columns = DbUtils::cleanColumns(array_keys($bindings));
        $this->bindings = array_values($bindings);

        $this->command = "INSERT INTO %s(%s)VALUES(%s);";

        $this->command = sprintf($this->command, $this->table,
            DbUtils::stringify($this->columns),
            DbUtils::placeholders(count($this->columns))
        );

        $this->prevClause = 'insert';

        return $this;
    }
}