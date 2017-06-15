<?php
namespace core;

use \Exception;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Query;
use MongoDB\Driver\Command;

/**
 * @author Jason Wright <jason@silvermast.io>
 * @since 1/4/17
 * @package charon
 */
abstract class Model {
    use Singleton;
    use db\Mongo;

    const ID    = 'id';
    const TABLE = 'default'; // override

    /** @var mixed */
    protected static $_indexes;

    /**
     * Base constructor.
     * @param array $vars
     */
    public function __construct($vars = []) {
        $this->setVars($vars);
    }

    /**
     * @param array $vars
     * @return $this
     */
    public function setVars($vars = []) {
        if (is_object($vars)) $vars = get_object_vars($vars);
        foreach ($vars as $key => $val)
            if (property_exists($this, $key)) $this->$key = $val;

        return $this;
    }

    /**
     * @throws Exception
     * @return self
     */
    public abstract function validate();

    /**
     * Saves the object
     * @throws Exception
     * @return self
     */
    public function save() {
        try {

            // create a new transaction batch
            $bulkWrite = new BulkWrite();
            if (empty($this->{static::ID})) {
                // INSERT
                // generate a new unique ID
                while (empty($this->{static::ID})) {
                    $id_value = static::generateId();
                    // barbaric collision handling
                    if (!self::findOne([static::ID => $this->{static::ID}]))
                        $this->{static::ID} = $id_value;
                }

                $bulkWrite->insert($this);

            } else {
                // UPDATE
                $bulkWrite->update([static::ID => $this->{static::ID}], $this);

            }

            // commit the changes
            self::_db()->executeBulkWrite(static::getDBNamespace(), $bulkWrite);

        } catch (Exception $e) {
            Debug::error($e->getMessage());
            throw new Exception('Unable to save the item.', 500);
        }

        return $this;
    }

    /**
     * Deletes this object from the database
     * @return self
     */
    public function delete() {
        try {
            $bulkWrite = new BulkWrite();
            $bulkWrite->delete([static::ID => $this->{static::ID}]);
            $db = self::_db();
            $db->executeBulkWrite(static::getDBNamespace(), $bulkWrite);

        } catch (Exception $e) {

        }
        return $this;
    }

    /**
     * Simple web-safe random ID string generator
     * @return string
     */
    public static function generateId() {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $len   = strlen($chars) - 1;
        $id    = '';
        for ($i = 0; $i < 16; $i++)
            $id .= $chars[mt_rand(0, $len)];
        return $id;
    }

    /**
     * Finds a single object
     * @param $query
     * @param array $queryOptions
     * @return static|null
     */
    public static function findOne($query, $queryOptions = []) {
        try {
            $queryOptions['limit'] = 1;
            $dbQuery = new Query($query, $queryOptions);
            $cursor = self::_db()->executeQuery(static::getDBNamespace(), $dbQuery)->toArray();
            return current($cursor) ? static::new(current($cursor)) : null;

        } catch (Exception $e) {
            Debug::error($query);
        }

        return null;
    }

    /**
     * Returns an array of objects (in memory)
     * @param $query
     * @param array $queryOptions
     * @return static[]
     */
    public static function findMulti($query, $queryOptions = []) {
        $objects = [];
        try {
            $dbQuery = new Query($query, $queryOptions);
            $result = self::_db()->executeQuery(static::getDBNamespace(), $dbQuery);

            foreach ($result as $row)
                $objects[$row->{static::ID}] = static::new($row);

        } catch (Exception $e) {
            Debug::error($query);
        }
        return $objects;
    }

    /**
     * Returns the number of objects matching the query
     * @param $query
     * @param array $queryOptions
     * @return int
     */
    public static function count($query, $queryOptions = []) {
        $count = 0;
        Debug::info($query);
        try {
            $dbQuery = new Query($query, $queryOptions);
            $result = self::_db()->executeQuery(static::getDBNamespace(), $dbQuery);

            foreach ($result as $row)
                $count++;

        } catch (Exception $e) {
            Debug::error($query);
        }
        return $count;
    }

    /**
     * Deletes objects from the table
     * @param $query
     * @return $this
     * @throws Exception
     */
    public static function deleteQuery(array $query) {
        if (!count($query))
            throw new Exception('Must provide a valid delete query.');

        try {
            $bulkWrite = new BulkWrite();
            $bulkWrite->delete($query);
            self::_db()->executeBulkWrite(static::getDBNamespace(), $bulkWrite);

        } catch (Exception $e) {
            Debug::error($e->getMessage());
        }
    }

}