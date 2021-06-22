<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2019/04/03
 * Time: 09:36 PM
 */

namespace Sifra\Siorm\Sql;


class DeleteStatement extends FindableStatement
{
    protected $commandType = Statement::COMMAND_DELETE;

    public function __construct($table)
    {
        parent::__construct($table);

        $this->command = "DELETE FROM {$this->table}";
        $this->prevClause = "delete";
    }

    protected function canFind()
    {
        return $this->prevClause == 'delete';
    }
}