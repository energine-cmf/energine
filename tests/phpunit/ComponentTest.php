<?php
// loading fake Document class for component run
require_once('gears/Document.class.php');

/**
 * Class ComponentTest
 */
class ComponentTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Component
     */
    private $component;

    public function setUp() {
        $this->component = new Component('phpunit', 'test');
    }

    public function testCreateComponent() {

    }

    public function testLoadConfig() {

    }

    public function testBuild() {

    }

    public function testSetParam() {

    }

    public function testDetermineState() {

    }

    public function testRun() {

    }
}