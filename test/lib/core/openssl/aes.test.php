<?php
namespace test\core\crypto;
/**
 * @author Jason Wright <jason@silvermast.io>
 * @since 1/11/17
 * @package charon
 */

require_once(__DIR__ . '/../../../../core.php');

use core;

class AESTest extends \PHPUnit_Framework_TestCase {

    /**
     * Tests client priv -> pub
     */
    public function test() {
        $data = 'this is a very secure password that needs to be stored';

        $cipher = core\crypto\AES::encrypt($data);
        $result = core\crypto\AES::decrypt($cipher);

        $this->assertEquals($data, $result);

    }

}
