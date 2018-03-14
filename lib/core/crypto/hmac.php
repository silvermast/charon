<?php
namespace core\crypto;

/**
 * Hashing class for verifying encryption tags
 * @author Jason Wright <jason.dee.wright@gmail.com>
 * @since 2/15/17
 * @package charon
 */
class HMAC {

    const SALT = 'charon.hmac';

    /**
     * Generates an HMAC tag
     * @param string $data (base64)
     * @param string $key
     * @return string
     */
    public static function getTag(string $data, string $key) {
        $hashKey = self::hashKey($key);
        return base64_encode(hash_hmac('sha256', base64_decode($data), $hashKey, true));
    }

    /**
     * Verifies the provided data/key with a tag
     * @param string $data
     * @param string $key
     * @param string $tag
     * @return bool
     */
    public static function verifyTag(string $data, string $key, string $tag) {
        return self::getTag($data, $key) === $tag;
    }

    /**
     * Passes the key through a pbkdf2 hash
     * @param $key
     * @param string $salt
     * @return mixed
     */
    public static function hashKey($key) {
        return hex2bin(hash_pbkdf2('sha256', $key, self::SALT, 10));
    }

}