<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2018/12/25
 * Time: 11:15
 */

namespace Sifra\Siorm\Models;


use ReflectionClass;
use RuntimeException;
use DirectoryIterator;
use Sifra\Core\BaseObject;
use Sifra\Siorm\Queryable;
use InvalidArgumentException;
use Sifra\Siorm\QueryBuilder;
use Sifra\Siorm\Util\DbUtils;
use Sifra\Siorm\Util\Reflector;
use Sifra\Core\Exception\NotFoundException;
/**
 * A Model represents a database object
 */
abstract class Model extends BaseObject
{
    protected $table;
    protected $key = 'id';
    protected $hasGeneratedKey = true;
    protected $columns = array();
    protected $hasTimestamps = false;
    protected $timestamps = array();

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes, true);
    }

    public function getKey()
    {
        return $this->getAttribute( static::meta()->keyName() );
    }

    /**
     * @return array|bool|\Sifra\Siorm\Models\Model
     */
    public function save()
    {
        if (!static::exists($this->getKey())) {
            return self::create($this->attributes);
        }

        static::addTimestamp('save', $this->attributes);

        QueryBuilder::table(static::meta()->table())
            ->update()
            ->values($this->attributes)
            ->where(static::meta()->keyName(), $this->getKey())
            ->exec();

        return $this;
    }

    public function destroy()
    {
        return static::delete($this->getKey());
    }

    protected function belongsTo($target, $foreignKey = null, $foreignKeyTarget = null)
    {
        if (!Reflector::isModel($target)) {
            throw new InvalidArgumentException('$target must be a \Sifra\Siorm\Models\Model');
        }

        $foreignKeyTarget = $foreignKeyTarget ?: $target::meta()->keyName();

        if (!$foreignKey && !$this->hasAttribute($foreignKey)) {
            $foreignKey = $target::meta()->singularName() . '_' . $foreignKeyTarget;
        }

        return $target::where($foreignKeyTarget, $this->getAttribute($foreignKey))
            ->get()
            ->first();
    }

    protected function hasMany($target, $foreignKey = null, $foreignKeyTarget = null)
    {
        if (!Reflector::isModel($target)) {
            throw new InvalidArgumentException('$target must be a \Sifra\Siorm\Models\Model');
        }

        $foreignKeyTarget = $foreignKeyTarget ?: $target::meta()->keyName();

        if (!$foreignKey && !$this->hasAttribute($foreignKey)) {
            $foreignKey = static::meta()->singularName() . '_' . $foreignKeyTarget;
        }

        return $target::where($foreignKey, $this->getAttribute($foreignKeyTarget));
    }

    public static function create($data)
    {
        if (!(is_array($data) || $data instanceof Model)) {
            throw new InvalidArgumentException('Model or array expected');
        }

        $data = $data instanceof Model ? $data->toArray() : $data;

        // Removed the user provided ID if IDs are auto incremented
        if (static::meta()->hasGeneratedKey()) {
            unset($data[ static::meta()->keyName() ]);
        }

        self::addTimestamp('create', $data);

        $result = QueryBuilder::table(static::meta()->table())
            ->insert()
            ->values($data)
            ->getResultSet();

        if ($result->getRowCount()) {
            $key = static::meta()->hasGeneratedKey() && $result->getInsertId() ? $result->getInsertId() : $data[ static::meta()->keyName() ];

            if (settings('siorm.models.on_create') == 'model') {
                return static::find($key);
            }

            return $key;
        }

        return null;
    }

    /**
     * @param mixed $key
     * @return \Sifra\Siorm\Models\Model
     */
    public static function find($key)
    {
        return QueryBuilder::table(static::meta()->table())
            ->select()
            ->where(static::meta()->keyName(), $key)
            ->get(static::class)
            ->first();
    }

    /**
     * @param mixed $key
     * @return \Sifra\Siorm\Models\Model
     */
    public static function findBy($column, $value)
    {
        return QueryBuilder::table(static::meta()->table())
            ->select()
            ->where($column, $value)
            ->get(static::class)
            ->first();
    }

    public static function delete($key)
    {
        return QueryBuilder::table(static::meta()->table())
            ->delete()
            ->where(static::meta()->keyName(), $key)
            ->exec();    }

    public static function deleteByKey($key)
    {
        return QueryBuilder::table(static::meta()->table())
            ->delete()
            ->where(static::meta()->keyName(), $key)
            ->execute();
    }

    public static function where($column, $operator, $value = null)
    {
        if (func_num_args() == 2) {
            $value = $operator;
            $operator = '=';
        }

        // Start the query chain. Fluent
        return (new Queryable(static::class))
            ->where($column, $operator, $value);
    }

    public static function all(array $columns = array())
    {
        return (new Queryable(static::class))->select($columns);
    }

    public static function exists($key)
    {
        $column = static::meta()->keyName();

        if (func_num_args() > 1) {
            $column = func_get_arg(0);
            $key = func_get_arg(1);
        }

        return (bool) QueryBuilder::table(static::meta()->table())
            ->select()
            ->count()
            ->where($column, $key)
            ->get();
    }

    private static function addTimestamp($on, array &$data = array())
    {
        $on = strtolower($on);

        if (!in_array($on, ['create', 'save'])) {
            throw new InvalidArgumentException('$on must be create or save.');
        }

        if (static::meta()->hasTimestamps()) {
            if (count(static::meta()->timestamps())) {
                if (isset(static::meta()->timestamps()[$on])) {
                    foreach (static::meta()->timestamps() as $tc) {
                        if (!empty(trim($tc))) {
                            $data[trim($tc)] = date(settings('siorm.models.datetime_format'));
                        }
                    }
                }
            } else {
                if ($on == 'create') {
                    $data['created_at'] = date(settings('siorm.models.datetime_format'));
                    $data['updated_at'] = date(settings('siorm.models.datetime_format'));
                } else if ($on == 'save') {
                    $data['updated_at'] = date(settings('siorm.models.datetime_format'));
                }
            }
        }
    }

    public static function isModel($class)
    {
        return key_exists(self::class, class_parents($class, true));
    }

    public function config() {
        return [
            'table' => trim($this->table) ?: DbUtils::makeTableName(static::class),
            'key' => $this->key,
            'hasGeneratedKey' => $this->hasGeneratedKey,
            'columns' => $this->columns,
            'class' => static::class,
            'hasTimestamps' => $this->hasTimestamps,
            'timestamps' => $this->timestamps,
        ];
    }

    public static function meta()
    {
        return Reflector::get(static::class);
    }
}