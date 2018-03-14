<?php
namespace core;

/**
 *
 * @author Jason Wright <jason.dee.wright@gmail.com>
 * @since 2/17/17
 * @package charon
 */
class Template {

    /**
     * Outputs a template file
     * @param string $file
     * @param array $data
     */
    public static function output(string $file, array $data = []) {
        extract($data);
        @include(HTML . "/templates/$file");
    }

    /**
     * Gets template output as a string
     * @param string $file
     * @param array $data
     * @return string
     */
    public static function get(string $file, array $data = []) {
        ob_start();
        self::output($file, $data);
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }

}