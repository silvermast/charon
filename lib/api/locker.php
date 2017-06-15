<?php
namespace api;

use core;
use models;
use \Exception;
use \stdClass;

/**
 *
 * @author Jason Wright <jason@silvermast.io>
 * @since 1/3/17
 * @package charon
 */
class Locker extends core\APIRoute {

//    protected $is_encrypted = false;

    /**
     * Reads a locker object
     * /locker/:index_id
     */
    public function get() {
        $index_id = $this->path[0] ?? false;
        if (!$index_id) {
            require(HTML . '/locker.php');
            die();

        } elseif ($index_id === '_index') {
            // calling /locker/_index pulls a sorted index array
            $data = $this->_get_index();

        } else {
            // load the locker data object
            $data = models\Locker::findOne(['id' => $index_id, 'accountId' => models\Account::current()->id]);
        }

        $this->send($data);
    }

    /**
     * Saves the Locker object
     * @throws Exception
     */
    public function post() {
        if (!$this->data instanceof stdClass)
            throw new Exception('Invalid Request Object', 400);

        $this->data->accountId = models\Account::current()->id;

        $locker = models\Locker::new($this->data)->validate()->save();

        // send the response
        $this->send($locker);
    }

    /**
     * Deletes the Locker object
     */
    public function delete() {
        if (isset($this->path[0]))
            models\Locker::new(['id' => $this->path[0], 'accountId' => models\Account::current()->id])->delete();

        $this->send('Successfully deleted Locker.');
    }

    /**
     * @return array
     */
    private function _get_index(): array {
        $lockers = models\Locker::findMulti(['accountId' => models\Account::current()->id], ['sort' => ['name' => 1]]);
        return array_values($lockers);
    }
}