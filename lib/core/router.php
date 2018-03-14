<?php
namespace core;

use models;
use api;
use \Exception;

/**
 * Base router class
 * @author Jason Wright <jason@silvermast.io>
 * @since 1/2/17
 * @package charon
 */
class Router {

    /**
     * @var array
     */
    private static $_routes = [
        'handshake'              => 'api\\Handshake',
        'login'                  => 'api\\Login',
        'logout'                 => 'api\\Logout',
        'locker'                 => 'api\\Locker',
        'user'                   => 'api\\User',
        'request-password-reset' => 'api\\RequestPasswordReset',
    ];

    /**
     * Routes the request to the corresponding subclass
     */
    public static function route() {
        if (!models\Account::current())
            Response::send('We were unable to find an account under the given URL.', 404);

        if (!$path = trim($_SERVER['REQUEST_URI'], '/')) {
            header('Location: /locker');
            die();
        }

        $apiRoute = self::_getAPIROute($path);
        $method   = strtolower($_SERVER['REQUEST_METHOD']);

        if (!method_exists($apiRoute, $method))
            Response::send('Method does not exist.', 405);

        try {
            $apiRoute->$method();
        } catch (Exception $e) {
            Response::send($e->getMessage(), $e->getCode());
        }

        Response::send('An error occurred while we were routing your request.', 500);
    }

    /**
     * Factory APIRoute method
     * @param string $path
     * @return APIRoute
     */
    private static function _getAPIRoute(string $path) {
        $path      = preg_split('@[/|\?]@', $path);
        $classname = array_shift($path); // example: "url.com/classname/foo/bar

        // first check the static route list
        if (isset(self::$_routes[$classname]))
            return new self::$_routes[$classname]($path);

        // next, try checking for an API class
        // example: "/manage-users" -> "ManageUsers"
        $classname = mb_strtolower($classname);
        $classname = preg_replace('@[^\w\d]+@', ' ', $classname); // all classnames are alphanumeric
        $classname = ucwords($classname); // capital case
        $classname = str_replace(' ', '', $classname); // strip out spaces
        if (class_exists($apiRoute = "api\\$classname"))
            return new $apiRoute($path);

        Response::send('Page not found.', 404);
    }

}