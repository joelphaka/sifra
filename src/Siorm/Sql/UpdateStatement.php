<?php
/**
 * @author Joël Phaka
 * Date: 2019/04/03
 * Time: 09:22 PM
 */

namespace Sifra\Siorm\Sql;


use InvalidArgumentException;
use Sifra\Siorm\Util\DbUtils;

class UpdateStatement extends FindableStatement
{
    protected $commandType = Statement::COMMAND_UPDATE;

    public function values(array $bindings)
    {
        if ($this->prevClause) return $this;

        if (!count($bindings)) {
            throw new InvalidArgumentException('$bindings cannot be an empty array.');
        }

        $this->columns = DbUtils::cleanColumns(array_keys($bindings));
        $this->bindings = array_values($bindings);

        $this->command = "UPDATE {$this->table} SET " . DbUtils::stringifyForUpdate($this->columns);

        $this->prevClause = "update";

        return $this;
    }

    protected function canFind()
    {
        return $this->prevClause == 'update';
    }
}