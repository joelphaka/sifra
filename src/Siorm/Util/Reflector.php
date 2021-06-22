<?php
namespace Sifra\Siorm\Util;

use Sifra\Siorm\Models\Model;
use \RuntimeException;
use \ReflectionClass;

class Reflector
{
    private $class;
    private $table;
    private $key;
    private $columns;
    private $hasGeneratedKey;
    private $hasTimestamps;
    private $timestamps;

    private static $reflections = [];

    public function __construct($class)
    {
        if (!self::isModel($class)) {
            throw new RuntimeException("{ {$class} } is not a Sifra\Siorm\Models\Model");
        }

        $this->class = $class;

        $instance = new $class();
        $config = (object)$instance->config();


        $this->table = $config->table ?: self::makeTableName($class);


        $this->key = $config->key;
        $this->columns = $config->columns;
        $this->hasGeneratedKey = $config->hasGeneratedKey;
        $this->hasTimestamps = $config->hasTimestamps;
        $this->timestamps = $config->timestamps;
    }

    public function getTableName()
    {
        return $this->table;
    }

    public function table()
    {
        return $this->table;
    }

    public function getKeyName()
    {
        return $this->key;
    }

    public function keyName()
    {
        return $this->key;
    }

    public function singularName()
    {
        if (settings('siorm.models.pluralize')) {
            return substr_replace($this->table, '', strlen($this->table) - 1, 1);
        }

        return $this->table;
    }

    public function hasGeneratedKey()
    {
        return $this->hasGeneratedKey;
    }

    public function hasTimestamps()
    {
        return $this->hasTimestamps;
    }

    public function getTimestampNames()
    {
        return $this->timestamps;
    }

    public function timestamps()
    {
        return self::getTimestampNames();
    }

    public static function isModel($class)
    {
        return key_exists(Model::class, class_parents($class, true));
    }

    private static function makeTableName($class)
    {
        $tableName = (new ReflectionClass($class))->getShortName();

        if (settings('siorm.models.pluralize')) {
            $tableName = $tableName . 's';
        }

        return strtolower($tableName);
    }

    /**
     * @param $class
     * @return null|\Sifra\Siorm\Util\Reflector
     */
    public static function get($class)
    {
        return self::reflectors($class);
    }

    public static function reflectors($class = null)
    {
        if (!count(self::$reflections)) {

            DbUtils::getModels(function ($class) {
                self::$reflections[$class] = new Reflector($class);
            });
        }

        if (func_num_args() == 1) {

            if (isset(self::$reflections[ $class ])) {

                return self::$reflections[ $class ];
            }

            return null;
        }

        return self::$reflections;
    }
}