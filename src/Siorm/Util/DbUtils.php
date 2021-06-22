<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2019/01/21
 * Time: 21:28
 */

namespace Sifra\Siorm\Util;

use \PDO;
use \Exception;
use \InvalidArgumentException;
use \RuntimeException;
use Sifra\Core\Env;
use \stdClass;

final class DbUtils
{
    const OPERATORS = ['<','>','=', '<=','>=','LIKE','!=', '<>'];

    public static function createConnection(array $options)
    {
        $options = array_change_key_case($options, CASE_UPPER);

        if (!isset($options['DB_DRIVER'])) {
            throw new RuntimeException("No 'DB_DRIVER' specified");
        }

        $options['DB_DRIVER'] = strtolower($options['DB_DRIVER']);

        if ($options['DB_DRIVER'] == 'mysql') {
            return self::createMySQLConnection($options);

        } else if ($options['DB_DRIVER'] == 'sqlite') {
            return self::createSQLiteConnection($options);
        }
    }

    public static function createMySQLConnection(array $options)
    {
        if (!isset($options['DB_HOST'])) {
            throw new InvalidArgumentException("'DB_HOST' is required for MySQL");
        } else if (!isset($options['DB_NAME'])) {
            throw new InvalidArgumentException("'DB_NAME' is required for MySQL");
        } else if (!isset($options['DB_USER'])) {
            throw new InvalidArgumentException("'DB_USER' is required for MySQL");
        }

        $pdo = null;

        if (!isset($options['DB_PASSWORD'])) {
            $pdo =  new PDO("mysql:host={$options['DB_HOST']};dbname={$options['DB_NAME']};charset=utf8",
                $options['DB_USER']
            );
        } else {
            $pdo =  new PDO("mysql:host={$options['DB_HOST']};dbname={$options['DB_NAME']};charset=utf8",
                $options['DB_USER'],
                $options['DB_PASSWORD']
            );
        }

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    public function createSQLiteConnection(array $options)
    {
        if (!isset($options['DB_FILE'])) {
            throw new InvalidArgumentException("'DB_FILE' is required for SQLite");
        }

        $pdo =  new PDO("sqlite:{$options['DB_FILE']}");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    public static function getValueType($value)
    {
        if (is_numeric($value)) {
            return PDO::PARAM_INT;
        } elseif (is_bool($value)) {
            return PDO::PARAM_BOOL;
        } elseif (is_object($value) || is_array($value) || is_resource($value)) {
            return PDO::PARAM_LOB;
        } elseif (is_null($value)) {
            return PDO::PARAM_NULL;
        } else {
            return PDO::PARAM_STR;
        }
    }

    public static function cleanColumn($name)
    {
        $name = trim($name, ',; ');

        if (empty($name)) {
            throw new InvalidArgumentException('Invalid column: ' . $name);
        }

        return $name;
    }

    public static function cleanColumns(array $columns)
    {
        return array_map(['self', 'cleanColumn'], $columns);
    }

    public static function stringify(array $columns)
    {
        return implode(',', array_map(function ($column) {

            return self::cleanColumn($column);

        }, $columns));
    }

    public static function stringifyForUpdate(array $columns)
    {
        return implode(',', array_map(function ($column) {

            return self::cleanColumn($column) . '=?';

        }, $columns));
    }

    public static function placeholders($n)
    {
        $n = (int)$n;

        if ($n < 1) {
            throw new InvalidArgumentException('Number of placeholders must be greater than 0. Given: ' . $n);
        }

        return rtrim( str_repeat('?,', $n), ', ' );
    }

    /**
     * This loads all of the Model classes
     *
     * @param \Closure $handler
     * @return void
     */
    public static function getModels(\Closure $handler = null)
    {
        $modelsDir = settings('siorm.models.dir');

        if (!is_dir($modelsDir)) {
            throw new RuntimeException("Directory { {$modelsDir} } Not Found.");
        }

        $classes = [];

        foreach (glob($modelsDir . '/*.php') as $filepath) {
            
            $class = settings('siorm.models.namespace') . '\\' . pathinfo($filepath, PATHINFO_FILENAME);

            $class = ltrim($class, '\\');
            $classes[] = $class;

            if ($handler) {
                call_user_func_array($handler, [$class]);
            }
        }

        return $classes;
    }

    public static function makeTableName($class)
    {
        $tableName = (new ReflectionClass($class))->getShortName();

        if (settings('siorm.models.pluralize')) {
            $tableName = $tableName . 's';
        }

        return strtolower($tableName);
    }
}