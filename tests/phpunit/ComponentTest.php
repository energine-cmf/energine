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
        $this->component = new Component('phpunit', 'test', array('config' => self::TEST_CONFIG_XML));
        $getConfigMethod = $this->getComponentMethod('getConfig');
        $config = $getConfigMethod->invoke($this->component);
        $this->assertInstanceOf('ComponentConfig', $config);
        //stop($config->getStateConfig('main'));
    }

    public function testBuild() {

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

    }

    public function testRun() {

    }

    /**
     * Method to test protected and private methods
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