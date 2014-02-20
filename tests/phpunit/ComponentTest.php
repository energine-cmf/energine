<?php
/**
 * Class ComponentTest
 */
class ComponentTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Component
     */
    private $component;

    /**
     * @var ComponentManager
     */
    private $cm;

    /**
     * @var Document
     */
    private $document;

    /**
     * @var DocumentController
     */
    private $documentController;

    public function setUp() {
        $this->documentController = new DocumentController();
        $this->document = new Document();
        $this->cm = new ComponentManager($this->document);
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