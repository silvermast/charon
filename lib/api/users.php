<?php
namespace api;

use core;
use models;
use \Exception;
use \stdClass;

/**
 * /manage-users API path
 * @author Jason Wright <jason@silvermast.io>
 * @since 1/3/17
 * @package charon
 */
class Users extends core\APIRoute {

    protected $is_encrypted = true;

    /**
     * GET /manage-users
     * Reads the authenticated user's manage-users
     */
    public function get() {
        $this->checkPermission([models\User::PERMLEVELS['Admin']]);

        if (!$this->is_json) {
            require(HTML . '/users.php');
            die();

        } else {

            if (isset($this->path[0])) {
                $this->send(models\User::findOne(['id' => $this->path[0], 'accountId' => models\Account::current()->id]));

            } else {
                // load the locker data object
                $this->send(models\User::findMulti(['accountId' => models\Account::current()->id]));
            }

        }
    }

    /**
     * POST /manage-users/<user-id>
     * Saves the user's manage-users information
     * @throws Exception
     */
    public function post() {
        // only admins and above are allowed here
        $this->checkPermission([models\User::PERMLEVELS['Admin']]);

        if (!$this->data instanceof stdClass)
            throw new Exception('Invalid Request Object', 400);

        if (isset($this->path[0])) {
            $user = models\User::findOne(['id' => $this->path[0]]);
            if (!$user->id)
                throw new Exception('Unable to find that user.', 404);
        }

        $user = $user ?? models\User::new();

        // Check user permissions against object being saved.
        switch (models\User::me()->permLevel) {
            // only Owners can modify Owners/Admins
            case PERMLEVEL_OWNER:
                break;

            case PERMLEVEL_ADMIN:
                if ($user->permLevel == PERMLEVEL_OWNER)
                    throw new Exception('You do not have permission to do edit Owner users.', 400);
                break;

            case PERMLEVEL_MEMBER:
            default:
                throw new Exception('You do not have permission to do edit other users.', 400);
                break;
        }

        // check if email already exists
        $existingUserCount = models\User::count([
            'accountId' => models\Account::current()->id,
            'id'        => ['$ne' => $user->id],
            'email'     => $this->data->email,
        ]);
        if ($existingUserCount)
            throw new Exception('A User already exists with this email address.', 400);

        // set password
        if (!empty($this->data->changePass1)) {
            if ($this->data->changePass1 !== $this->data->changePass2)
                throw new Exception('Passwords do not match', 400);
            else
                $user->setPassword($this->data->changePass1);
        }

        // set other data
        $user->setVars($this->data);
        $user->accountId = models\Account::current()->id;

        $user->validate()->save();

        $this->send($user);
    }

    /**
     * DELETE /manage-users/<user-id>
     * @throws Exception
     */
    public function delete() {
        // only admins and above are allowed here
        $this->checkPermission([models\User::PERMLEVELS['Admin']]);

        if (!isset($this->path[0]))
            throw new Exception('Invalid User ID', 400);

        if (!$user = models\User::findOne(['id' => $this->path[0], 'accountId' => models\Account::current()->id]))
            throw new Exception('Unable to find that user.', 404);


        // Check user permissions against object being saved.
        switch ($user->permLevel) {
            // only Owners can modify Owners/Admins
            case models\User::PERMLEVELS['Owner']:
            case models\User::PERMLEVELS['Admin']:
                $this->checkPermission();
                break;

            case models\User::PERMLEVELS['Member']:
            default:
                break;
        }

        $otherOwners = models\User::count([
            'accountId' => models\Account::current()->id,
            'permLevel' => models\User::PERMLEVELS['Owner'],
            'id'        => ['$ne' => $user->id],
        ]);

        if (!$otherOwners)
            throw new Exception('This user is the last Owner in the account. Each Account must have at least one owner.');

        $user->delete();
        $this->send("Successfully deleted $user->name");
    }

}