<?php
namespace core;
/**
 * Debug class responsible for logging items in /log
 * @author Jason Wright <jason@silvermast.io>
 * @since 2/18/15
 * @package charon
 */

class Debug {

    /**
     * @param mixed $message
     */
    public static function info($message) {
        self::write($message, __FUNCTION__);
    }

    /**
     * @param mixed $message
     */
    public static function error($message) {
        self::write($message, __FUNCTION__);
    }

    /**
     * @param $message
     * @param $type
     */
    private static function write($message, $type) {
        $message = $message instanceof \Exception ? get_class($message) . ' - ' . $message->getMessage() : $message;
        $message = is_scalar($message) ? $message : json_encode($message);

        if (!is_dir(ROOT . '/log'))
            @mkdir(ROOT . '/log');

        @error_log(date(DATE_ATOM) . " -- $message\n", 3, ROOT . "/log/$type.log");
    }
}
