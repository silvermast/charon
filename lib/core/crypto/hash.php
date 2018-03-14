<?php
namespace core\crypto;

/**
 * Hash wrapper class for all iteration & salt combos
 * @author Jason Wright <jason.dee.wright@gmail.com>
 * @since 3/13/18
 * @package silvermast
 */

class Hash {


    /**
     * @param $plaintext
     * @param null $i
     * @return mixed hex
     */
    public static function userSQ($plaintext, $i = null) {
        $salt = isset($i) ? "Charon.UserSQ.$i" : "Charon.UserSQ";
        return hash_pbkdf2('sha256', $plaintext, $salt, 1030);
    }

    /**
     * @param $plaintext
     * @return mixed hex
     */
    public static function userPassword($plaintext) {
        return hash_pbkdf2('sha256', $plaintext, 'Charon.UserKeychain.PassHash', 20);
    }

    /**
     * @param $plaintext
     * @return mixed hex
     */
    public static function contentKeyKey($plaintext) {
        return hash_pbkdf2('sha256', $plaintext, 'Charon.UserKeychain.ContentKeyKey', 15);
    }

}