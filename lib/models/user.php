<?php
namespace models;

use core;
use \Exception;

/**
 * @author Jason Wright <jason@silvermast.io>
 * @since 1/4/17
 * @package charon
 *
 * @property Account $Account
 * @property bool $sqIsValid
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
    public $contentKeyEncrypted;

    public $passwordResetToken;
    public $sq1Q;
    public $sq1A;
    public $sq2Q;
    public $sq2A;
    public $sq3Q;
    public $sq3A;
    public $sqContentKeyEncrypted;
    public $sqLastUpdate;

    /** @var User */
    private static $_me = null;

    /**
     * @param $field
     * @return mixed
     */
    public function __get($field) {
        switch ($field) {

            case 'Account':
                return $this->_magic->$field ?? $this->_magic->$field = Account::findOne(['id' => $this->accountId]);

            case 'sqIsValid':
                return $this->sq1Q && $this->sq1A && $this->sq2Q && $this->sq2A && $this->sq3Q && $this->sq3A;

            default:
                return false;
        }
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function validate() {
        if (mb_strlen($this->id) > 1024) throw new Exception('Invalid id', 400);
        if (mb_strlen($this->name) > 1024) throw new Exception('Invalid name', 400);
        if (mb_strlen($this->email) > 1024) throw new Exception('Invalid email', 400);
        if (empty($this->accountId)) throw new Exception('Invalid Account', 400);

        // sha256 = 64 bytes
        if (mb_strlen($this->passhash) != 64) throw new Exception('Invalid password hash', 400);
        if (!isset($this->contentKeyEncrypted->cipher)) throw new Exception('Invalid Content Key', 400);

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
     * @param $password
     * @return mixed
     */
    public static function hashPassword($password) {
        return core\crypto\Hash::userPassword($password);
    }

    /**
     * Hashes the password and sets it into the object
     * @param string $password plaintext
     * @return self
     * @throws Exception
     */
    public function setPassword($password) {
        if (mb_strlen($password) < 12) throw new Exception('Passwords must be at least 12 characters. Type whatever you\'d like, though!');
        $this->passhash = self::hashPassword($password);
        return $this;
    }

    /**
     * Stores the content key
     * @param string $password plaintext
     * @param string $contentKey plaintext
     * @return self
     */
    public function setContentKey($password, $contentKey) {
        // this is a secret key expanded directly from the plaintext password. It must not be stored anywhere.
        $contentKeyKey = hex2bin(core\crypto\Hash::contentKeyKey($password));
        $this->contentKeyEncrypted = core\crypto\AES::encrypt($contentKey, $contentKeyKey);

        return $this;
    }

    /**
     * Decrypts the Content Key with a user's password
     * @param string $password plaintext
     * @return string
     */
    public function getContentKey($password) {
        // this is a secret key expanded directly from the plaintext password. It must not be stored anywhere.
        $contentKeyKey = hex2bin(core\crypto\Hash::contentKeyKey($password));

        // this is a secret key reserved for account locker manipulation. Do not store or share anywhere.
        return hex2bin(core\crypto\AES::decrypt($this->contentKeyEncrypted, $contentKeyKey));
    }

}