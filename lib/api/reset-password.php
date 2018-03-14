<?php

namespace api;

use core;
use models;
use \Exception;
use \stdClass;

/**
 * /reset-password API path
 * @author Jason Wright <jason@silvermast.io>
 * @since 1/3/17
 * @package charon
 */
class ResetPassword extends core\APIRoute {

    /** @var bool */
    protected $is_encrypted = false;

    /** @var bool overrides parent */
    protected $require_auth = false;

    /**
     * @return models\User
     * @throws Exception
     */
    private function getUser() {
        if (empty($_REQUEST['token']))
            throw new Exception("Please provide a valid reset token.", 400);

        $user = models\User::findOne([
            'accountId'          => models\Account::current()->id,
            'passwordResetToken' => $_REQUEST['token'],
        ]);

        if (!$user)
            throw new Exception('The provided token is invalid.', 404);

        // check token expiration
        $tokenRaw = core\Util::wsB64Decode($_REQUEST['token']);
        list($expires, $random) = explode('|', $tokenRaw);
        if (!$expires || (int)$expires < time())
            throw new Exception('The provided token is invalid.', 404);

        return $user;
    }

    /**
     * GET /profile
     * Reads the authenticated user's profile
     */
    public function get() {
        if (!$this->is_json) {
            require(HTML . '/reset-password.php');
            die();
        }

        $user = $this->getUser();

        // all is well. return the user object
        $this->send([
            'id'   => $user->id,
            'sq1Q' => $user->sq1Q,
            'sq2Q' => $user->sq2Q,
            'sq3Q' => $user->sq3Q,
        ]);
    }

    /**
     * POST /profile
     * Saves the user's profile information
     * @throws Exception
     */
    public function post() {

        $user = $this->getUser();

        if (empty($_REQUEST['sq1A']) || empty($_REQUEST['sq2A']) || empty($_REQUEST['sq3A']))
            throw new Exception("Please answer out all of the security questions.");

        if (empty($user->sqContentKeyEncrypted))
            throw new Exception("Error with User Security Question data.");

        $sq1A = mb_strtolower($_REQUEST['sq1A']);
        $sq2A = mb_strtolower($_REQUEST['sq2A']);
        $sq3A = mb_strtolower($_REQUEST['sq3A']);

        $response = [
            'pwSuccess' => false,
            'sqSuccess' => false,
            'sq1Error'  => core\crypto\Hash::userSQ($sq1A, 1) !== $user->sq1A,
            'sq2Error'  => core\crypto\Hash::userSQ($sq2A, 2) !== $user->sq2A,
            'sq3Error'  => core\crypto\Hash::userSQ($sq3A, 3) !== $user->sq3A,
        ];

        $response['sqSuccess'] = !$response['sq1Error'] && !$response['sq2Error'] && !$response['sq3Error'];

        // we don't have an error. Process the changed password?
        if ($response['sqSuccess'] && !empty($_REQUEST['changePass1'])) {

            if ($_REQUEST['changePass1'] !== $_REQUEST['changePass2'])
                throw new Exception('Passwords do not match.');

            try {

                // decrypt the contentKey and store it back in the user object
                $sqContentKeyKey = hex2bin(core\crypto\Hash::userSQ("$sq1A|$sq2A|$sq3A"));
                if (!$contentKey = core\crypto\AES::decrypt($user->sqContentKeyEncrypted, $sqContentKeyKey))
                    throw new Exception("Failed to decrypt sqContentKeyEncrypted.");

                $user->setPassword($_REQUEST['changePass1']);
                $user->setContentKey($_REQUEST['changePass1'], $contentKey);
                $user->save();

                $response['pwSuccess'] = true;

            } catch (Exception $e) {
                $response['pwMessage'] = $e->getMessage();
                $response['pwSuccess'] = false;
                core\Debug::error($e);
            }

        }

        $this->send($response);

    }

}