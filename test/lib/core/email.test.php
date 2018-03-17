<?php
namespace test\core;
/**
 * @author Jason Wright <jason@silvermast.io>
 * @since 3/17/2018
 * @package charon
 */

require_once(__DIR__ . '/../../../core.php');

use core;

class EmailTest extends \PHPUnit_Framework_TestCase {

    /**
     * Tests email sending
     */
    public function test() {
        core\Email::send('admin@silvermast.io', 'Test Email', 'This is a test');
    }

}
