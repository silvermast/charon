<?php
namespace models;

use core;
use \Exception;

/**
 * @author Jason Wright <jason.dee.wright@gmail.com>
 * @since 1/4/17
 * @package charon
 */
abstract class Base {
    use core\Singleton;

    const ID    = 'id';
    const TABLE = 'default'; // override

    /**
     * Base constructor.
     * @param array $vars
     */
    public function __construct($vars = []) {
        if (is_object($vars)) $vars = get_object_vars($vars);
        foreach ($vars as $key => $val)
            if (property_exists($this, $key)) $this->$key = $val;
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
        $db       = core\SQLite::initWrite();
        $table    = static::TABLE;
        $id_field = static::ID;
        $id_value = $this->{static::ID};

        if (!isset($id_value)) {
            // INSERT
            // generate a new unique ID
            while (!isset($this->{static::ID})) {
                $id_value = static::generateId();
                // barbaric collision handling
                if ($db->querySingle("SELECT COUNT(*) FROM $table WHERE $id_field = '" . $db->escapeString($id_value) . "'") == 0)
                    $this->{static::ID} = $id_value;
            }

            $array  = get_object_vars($this);
            $fields = '`' . implode('`, `', array_keys($array)) . '`';
            $values = array_map([$db, 'escapeString'], array_values($array));
            $values = "'" . implode("', '", $values) . "'";
            if (!$db->exec("INSERT INTO $table ($fields) VALUES ($values)"))
                throw new Exception('Unable to create the object.', 500);

        } else {
            // UPDATE

            $sql_values = [];
            foreach ($this as $field => $value)
                $sql_values[] = "`$field` = '" . $db->escapeString($value) . "'";
            $sql_values = implode(', ', $sql_values);

            // try updating
            if (!$db->exec("UPDATE $table SET $sql_values WHERE $id_field = '$id_value'"))
                throw new Exception('Unable to update the object.', 500);

        }

        $db->close();
        return $this;
    }

    /**
     * Deletes this object from the database
     * @return self
     */
    public function delete(): self {
        $db     = core\SQLite::initWrite();
        $table  = static::TABLE;
        $where  = $db->prepare_and_statement([static::ID => $this->{static::ID}]);

        $db->exec("DELETE FROM $table WHERE $where");
        $db->close();

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
     * @return static|null
     */
    public static function findOne($query) {
        $db    = core\SQLite::initRead();
        $table = static::TABLE;
        $where = $db->prepare_and_statement($query);
        if ($result = $db->querySingle("SELECT * FROM $table WHERE $where LIMIT 1", true)) {
            $db->close();
            return new static($result);
        }

        return null;
    }

    /**
     * Returns an array of objects (in memory)
     * @param $query
     * @return static[]
     */
    public static function findMulti($query) {
        $db      = core\SQLite::initRead();
        $table   = static::TABLE;
        $where   = $db->prepare_and_statement($query);
        $objects = [];
        if ($result = $db->query("SELECT * FROM $table WHERE $where"))
            while ($row = $result->fetchArray(SQLITE3_ASSOC))
                $objects[$row[static::ID]] = new static($row);

        $db->close();

        return $objects;
    }

}