<?php
namespace core;

/**
 * @author Jason Wright <jason@silvermast.io>
 * @since 3/13/18
 */
class Util {

    /**
     * @param $param
     * @return mixed
     */
    public static function wsB64Encode($param) {
        return str_replace('+', '_', base64_encode($param));
    }

    /**
     * @param $param
     * @return bool|string
     */
    public static function wsB64Decode($param) {
        return base64_decode(str_replace('_', '+', $param));
    }

}