<?php
namespace core;

use models;

/**
 * base class for managing API requests
 * @author Jason Wright <jason@silvermast.io>
 * @since 1/2/17
 * @package charon
 */
abstract class APIRoute {

    /** @var array */
    protected $path;

    /** @var mixed */
    protected $data;

    /** @var bool */
    protected $require_auth = true;

    /** @var bool */
    protected $is_json = true;

    /** @var bool */
    protected $is_encrypted = true;

    /**
     * Base constructor.
     */
    public function __construct($path = null, $data = null) {
        try {
            $this->path    = $path;
            $this->data    = $data ?? $this->getPayload();
            $this->is_json = Response::isJson();

            $this->decryptPayload();
            $this->decodePayload();

            // check for auth
            if ($this->require_auth && !models\User::me()) {

                if (!$this->is_json) {
                    http_response_code(401);
                    require_once(HTML . '/login.php');
                    die();
                }

                Response::send('Please log in.', 401);
            }

        } catch (\Exception $e) {
            \core\Debug::error($e->getMessage());
            Response::send('There was an error processing your request.', 500);
        }
    }

    /**
     * @return string
     */
    public function getPayload() {
        return file_get_contents('php://input');
    }

    /**
     * decrypts the payload data
     */
    protected function decryptPayload() {
        if ($this->is_encrypted && $this->data && isset($_SESSION['AESKey']))
            $this->data = openssl\AES::decrypt($this->data, $_SESSION['AESKey']);
    }

    /**
     * decodes the payload data
     */
    protected function decodePayload() {
        if (is_string($this->data) && $data = json_decode($this->data))
            $this->data = $data;
    }

    /**
     * Determines whether the user is allowed to access this page
     * @param array $allowedUserLevels
     */
    protected function checkPermission(array $allowedUserLevels = []) {
        $allowedUserLevels[] = models\User::PERMLEVELS['Owner']; // always allow Owner access

        $this->is_encrypted = false;
        if (!in_array(models\User::me()->permLevel, $allowedUserLevels))
            $this->send('You do not have the necessary permissions', 403);
    }

    /**
     * Sends an HTTP response, output data, and stops execution.
     * @param $response
     * @param $code
     */
    protected function send($data = null, $code = 200) {

        if ($this->is_encrypted && isset($_SESSION['AESKey']))
            $data = openssl\AES::encrypt($data, $_SESSION['AESKey']);

        $data = is_scalar($data) ? $data : json_encode($data);
        Response::send($data, $code);


        die();
    }

}