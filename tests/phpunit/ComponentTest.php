<?php
// loading fake Document class for component run
require_once('gears/Document.class.php');

/**
 * Class ComponentTest
 */
class ComponentTest extends PHPUnit_Framework_TestCase {

    const TEST_CONFIG_XML = '<configuration>
        <state name="main">
            <uri_patterns>
                <pattern>/</pattern>
            </uri_patterns>
        </state>
        <state name="test">
            <uri_patterns>
                <pattern>/test/</pattern>
            </uri_patterns>
        </state>
        </configuration>';

    /**
     * @var Component
     */
    private $component;

    public function setUp() {
        // Changing URI to test active components. It should go BEFORE
        // component creation. Because during component creation system
        // components such as Response and URI are created and REQUEST_URI
        // can't be changed after.
        $_SERVER['REQUEST_URI'] = '/test/';
        $this->component = new Component('phpunit', 'test');
    }

    public function testCreateComponent() {
        $this->assertInstanceOf('Document', $this->component->document);
        // When component initialized alone, its state should be default, lets check it
        $this->assertEquals(Component::DEFAULT_STATE_NAME, $this->component->getState());
        // checking that default set of params is present
        $getParamMethod = $this->getComponentMethod('getParam');
        foreach(array('state', 'rights', 'config', 'active') as $param) {
            $this->assertNotNull($getParamMethod->invokeArgs($this->component, array($param)));
        }
    }

    public function testLoadConfig() {
        $this->component = new Component('phpunit', 'test', array('config' => new SimpleXMLElement(self::TEST_CONFIG_XML)));
        $getConfigMethod = $this->getComponentMethod('getConfig');
        $config = $getConfigMethod->invoke($this->component);
        // is config loaded
        $this->assertInstanceOf('ComponentConfig', $config);
        // By default component is inactive, so its should fallback to default state
        $this->assertEquals('main', $config->getCurrentStateConfig()->attributes()->name);
    }

    public function testBuild() {
        $result = $this->component->build();
        $this->assertInstanceOf('DOMDocument', $result);
        // Component node should be created at this moment
        $this->assertTrue($result->getElementsByTagName('component')->length > 0);
        /**
         * Counting on component, created in setUp method, we can check its properties after building component
         * @see setUp()
         */
        $this->assertEquals('phpunit', $result->getElementsByTagName('component')->item(0)->getAttribute('name'));
        $this->assertEquals('test', $result->getElementsByTagName('component')->item(0)->getAttribute('module'));
        $this->assertEquals('Component', $result->getElementsByTagName('component')->item(0)->getAttribute('class'));
    }

    /**
     * When there is no param defined, it cant be set
     *
     * @expectedException   SystemException
     */
    public function testSetNonExistantParam() {
        $setParamMethod = $this->getComponentMethod('setParam');
        $setParamMethod->invokeArgs($this->component, array('test', 'phpunit'));
    }

    /**
     *  Trying to change existant param
     */
    public function testSetParam() {
        $setParamMethod = $this->getComponentMethod('setParam');
        $getParamMethod = $this->getComponentMethod('getParam');
        $setParamMethod->invokeArgs($this->component, array('state', 'test'));
        $this->assertEquals('test', $getParamMethod->invokeArgs($this->component, array('state')));
        $setParamMethod->invokeArgs($this->component, array('state', 'test|test2|test3'));
        $this->assertEquals(array('test', 'test2', 'test3'), $getParamMethod->invokeArgs($this->component, array('state')));
    }

    public function testDetermineState() {
        $this->component = new Component('phpunit', 'test', array('active' => true, 'config' => new SimpleXMLElement(self::TEST_CONFIG_XML)));
        /**
         * Because of config, defined in
         * @see TEST_CONFIG_XML
         * state of active component should be 'test'
         * @see setUp()
         */
        $this->assertEquals('test', $this->component->getState());
    }

    /**
     * When component is active and there is no action, linked to
     * its current state, component should throw Exception.
     *
     * @expectedException   SystemException
     */
    public function testRunNoAction() {
        $this->component = new Component('phpunit', 'test', array('active' => true, 'config' => new SimpleXMLElement(self::TEST_CONFIG_XML)));
        $this->component->run();
    }

    /**
     * Method for testing protected and private methods
     *
     * @param $name
     * @return ReflectionMethod
     */
    protected function getComponentMethod($name) {
        $class = new ReflectionClass('Component');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
}