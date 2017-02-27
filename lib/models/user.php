<?php
namespace models;

use core;
use \Exception;

/**
 * @author Jason Wright <jason@silvermast.io>
 * @since 1/4/17
 * @package charon
 */
class User extends core\Model {

    const TABLE = 'users';

    const PERMLEVELS = [
        'Owner'  => 1,
        'Admin'  => 10,
        'Member' => 20,
    ];

    public $id;
    public $accountId;
    public $name;
    public $email;
    public $permLevel = 20;
    public $passhash;

    /** @var User */
    private static $_me = null;

    /**
     * @return $this
     * @throws Exception
     */
    public function validate() {
        if (mb_strlen($this->id) > 1024) throw new Exception('Invalid id', 400);
        if (mb_strlen($this->name) > 1024) throw new Exception('Invalid name', 400);
        if (mb_strlen($this->email) > 1024) throw new Exception('Invalid email', 400);
        if (empty($this->accountId)) throw new Exception('Invalid Account', 400);

        $hash_info = password_get_info($this->passhash);
        if (!$hash_info['algo']) throw new Exception('Invalid passhash', 400);

        if (self::findOne(['accountId' => $this->accountId, 'email' => $this->email, 'id' => ['$ne' => $this->id]]))
            throw new Exception('A user with that email already exists.', 400);

        return $this;
    }

    /**
     * Returns the authenticated user
     * @return User|null
     */
    public static function me() {
        if (!isset($_SESSION['user_id']))
            return null;

        if (!self::$_me instanceof self)
            self::$_me = self::findOne(['id' => $_SESSION['user_id']]);

        return self::$_me;
    }

    /**
     * returns encryption key
     * @return mixed
     */
    public static function getKey() {
        return $_SESSION['user_key'];
    }

    /**
     * Hashes the password and sets it into the object
     * @param $password
     * @return self
     */
    public function setPasswordHash($password) {
        if (mb_strlen($password) < 12) throw new Exception('Passwords must be at least 12 characters. Type whatever you\'d like, though!');
        $this->passhash = password_hash($password, PASSWORD_DEFAULT);

        return $this;
    }
}