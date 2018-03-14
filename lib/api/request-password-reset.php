<?php
namespace api;

use core;
use models;
use \Exception;
use \stdClass;

/**
 * /profile API path
 * @author Jason Wright <jason@silvermast.io>
 * @since 1/3/17
 * @package charon
 */
class RequestPasswordReset extends core\APIRoute {

    /** @var bool  */
    protected $is_encrypted = false;

    /** @var bool overrides parent */
    protected $require_auth = false;

    /**
     * GET /profile
     * Reads the authenticated user's profile
     */
    public function get() {
        throw new Exception('Page not found', 404);
    }

    /**
     * POST /profile
     * Saves the user's profile information
     * @throws Exception
     */
    public function post() {
        if (!$this->data instanceof stdClass)
            throw new Exception('Invalid Request Object', 400);

        unset($this->data->id, $this->data->accountId, $this->data->permLevel); // avoid spoofing these variables

        $user = models\User::findOne([
            'accountId' => models\Account::current()->id,
            'email'     => $this->data->email,
        ]);

        // notify the user if the email doesn't exist. Often they forgot which email they're using.
        if (!$user)
            throw new Exception("No user found with the email address {$this->data->email}");

        if (!$user->sqIsValid)
            throw new Exception("That user never filled out their security questions. Unfortunately, we'll be unable to help you reset the password.");

        // token expires after 24 hours
        $user->passwordResetToken = core\Util::wsB64Encode((time() + 86400) . '|' . random_bytes(64));
        $user->save();

        // send the email
        core\Email::request_password_reset($user);

        // send the response
        $this->send("A password reset request has been emailed to {$this->data->email}");
    }

}