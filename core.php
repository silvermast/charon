<?php
/**
 * Charon Core Include
 * @author Jason Wright <jason@silvermast.io>
 * @since Feb 18, 2015
 * @copyright 2015 Jason Wright
 */
define('VERSION', '2019-04-24.01');
ini_set('error_log', __DIR__ . '/log/error.log');
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_STRICT);

require_once(__DIR__ . '/config.php');

// check php version
if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 70000) {
    echo 'Silvermast requires PHP Version 7.0 or above.';
    die();
}

/**
 * Auto-load classes with default php autoloader
 */
require_once(__DIR__ . '/vendor/autoload.php'); // include composer packages
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/lib');
spl_autoload_extensions('.php');
spl_autoload_register('spl_autoload');

spl_autoload_register(function($class) {
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

    $path_snake = strtolower(trim(preg_replace(['/[A-Z]/', '@/_@'], ['_$0', '/'], $path), '_'));
    if (file_exists(ROOT . "/lib/$path_snake")) {
        include_once(ROOT . "/lib/$path_snake");
        return;
    }

    $path_chain = strtolower(trim(preg_replace(['/[A-Z]/', '@/-@'], ['-$0', '/'], $path), '-'));
    if (file_exists(ROOT . "/lib/$path_chain")) {
        include_once(ROOT . "/lib/$path_chain");
        return;
    }

});

define('PERMLEVEL_OWNER', 1);
define('PERMLEVEL_ADMIN', 10);
define('PERMLEVEL_MEMBER', 20);