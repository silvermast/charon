<?php
namespace core\crypto;

use core\Encoding;
use \Exception;

/**
 * Index class responsible storing a list of all items
 * @author Jason Wright <jason@silvermast.io>
 * @since 2/18/15
 * @package charon
 */
class AES {

    /** Default Encryption Mode */
    const METHOD = 'AES-256-CBC';

    /**
     * @param $pt
     * @param string $key
     * @return array
     */
    public static function encrypt($pt, $key = CRYPT_KEY) {

        // set the params
        $iv = self::_iv();
        $ct = openssl_encrypt($pt, self::METHOD, $key, OPENSSL_RAW_DATA, $iv);

        return [
            'iv'     => bin2hex($iv),
            'cipher' => bin2hex($ct),
//            'tag'    => HMAC::getTag($ct, $key),
        ];
    }

    /**
     * @param $obj
     * @param string $key
     * @return mixed
     * @throws Exception
     */
    public static function decrypt($obj, $key = CRYPT_KEY) {
        $success = true; // never return early to help prevent timing attacks

        // Verify item is a json object
        if (is_string($obj) && !$obj = json_decode($obj))
            throw new Exception(__METHOD__ . ' Failed to decode JSON', 500);

        // verify crypt is set
        if (!isset($obj->cipher))
            throw new Exception(__METHOD__ . ' Decryption object missing cipher param', 500);

        // verify iv is set
        if (!isset($obj->iv))
            throw new Exception(__METHOD__ . ' Decryption object missing iv param', 500);

//        // verify the tag
//        if (isset($obj->tag))
//            $success = HMAC::verifyTag($obj->cipher, $key, $obj->tag);

        // decrypt
        $result = openssl_decrypt(hex2bin($obj->cipher), self::METHOD, $key, OPENSSL_RAW_DATA, hex2bin($obj->iv));

        // never return early to help prevent timing attacks
        return $success ? $result : false;
    }

    /**
     * Generates the Initialization Vector
     */
    private static function _iv() {
        return openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::METHOD));
    }

    /**
     * Generates a random encryption key
     * @param int $length
     * @return string hex
     */
    public static function getRandomKey($length = 32) {
        return bin2hex(random_bytes($length));
    }

}

