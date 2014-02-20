<?php

/**
 * Tests also
 * @code
class Control;
@endcode
 * Class ButtonTest
 */
class ButtonTest extends PHPUnit_Framework_TestCase {

    const CONTROL_XML = '<control id="add" title="BTN_PHPUNIT" type="button"/>';
    /**
     * @var button
     */
    private $control;

    public function setUp() {
        $xml = new SimpleXMLElement(self::CONTROL_XML);
        $this->control = new Button('control');
        $this->control->loadFromXml($xml);
    }

    public function testLoadFromXml() {
        $this->assertEquals('BTN_PHPUNIT', $this->control->getTitle());
        $this->assertEquals('button', $this->control->getType());
        $this->assertEquals('add', $this->control->getID());
    }

    public function testBuild() {
        $result = $this->control->build();
        $this->assertInstanceOf('DOMElement', $result);
        $this->assertEquals('control', $result->nodeName);
    }
}