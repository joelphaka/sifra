<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2019/01/21
 * Time: 21:19
 */

namespace Sifra\Siorm;


use Sifra\Core\Env;
use Sifra\Siorm\Sql\Statement;
use Sifra\Siorm\Util\DbUtils;

final class DB
{
    const DRIVER_MYSQL = 1;
    const DRIVER_PGSQL = 2;
    const DRIVER_SQLITE = 3;

    private $pdo;
    private $driver;
    private static $db;

    public function __construct(array $options = array())
    {
        $options = count($options) ? $options : Env::current()->all();
        $options = array_change_key_case($options, CASE_UPPER);

        $this->pdo = DbUtils::createConnection($options);
        $this->driver = $options['DB_DRIVER'];
    }

    public static function getDefault()
    {
        if (!self::$db) {
            self::$db = new DB();
        }

        return self::$db;
    }

    public function execute($sql, $bindings = array())
    {
        $stm = $this->pdo->prepare($sql);

        foreach ($bindings as $k => $v) {
            $stm->bindValue($k + 1, $v, DbUtils::getValueType($v));
        }

        $stm->execute();

        return $stm;
    }

    public function executeSql($sql, $bindings = array())
    {
        $stm = $this->execute($sql, $bindings);
        $meta = array();
        
        if ($this->getDriver() == DB::DRIVER_MYSQL) {
            if ($this->pdo->lastInsertId()) {
                $meta['insert_id'] = $this->pdo->lastInsertId();
            }
        }

        return new ResultSet($stm, $meta);
    }

    public function executeStatement(Statement $statement)
    {
        return $this->executeSql($statement->getCommand(), $statement->getBindings());
    }

    public function getDriver()
    {
        if ($this->driver = 'mysql') return DB::DRIVER_MYSQL;
        else if ($this->driver = 'pgsql') return DB::DRIVER_PGSQL;
        else if ($this->driver = 'sqlite') return DB::DRIVER_SQLITE;

        return null;
    }



    public function __destruct()
    {
        $this->pdo = null;
    }
}