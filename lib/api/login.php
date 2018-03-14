<?php

namespace api;

use models;
use \Exception;
use core;

/**
 * Handles a login request
 * @author Jason Wright <jason@silvermast.io>
 * @since 1/2/17
 * @package charon
 */
class Login extends core\APIRoute {

    /** @var bool overrides parent */
    protected $require_auth = false;

    /**
     * Authenticates the user
     */
    public function post() {

        // validate params
        if (empty($this->data->email)) throw new Exception('Invalid Email', 401);
        if (empty($this->data->passhash)) throw new Exception('Invalid Password', 401);

        $query = [
            'email'     => $this->data->email,
            'passhash'  => $this->data->passhash,
            'accountId' => models\Account::current()->id,
        ];

        // Pull the user list into memory
        if (!$dbUser = models\User::findOne($query))
            throw new Exception('Incorrect User credentials', 401);

        $_SESSION['user_id'] = $dbUser->id;

        $this->send([
            'id'                  => $dbUser->id,
            'accountId'           => $dbUser->accountId,
            'name'                => $dbUser->name,
            'email'               => $dbUser->email,
            'permLevel'           => $dbUser->permLevel,
            'passhash'            => $dbUser->passhash,
            'contentKeyEncrypted' => $dbUser->contentKeyEncrypted,
        ], 200);

    }

}