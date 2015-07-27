<?php
/**
 * @file
 * Component, IBuilder.
 * It contains the definition to:
 * @code
class Component;
 * interface IBuilder;
 * @endcode
 * @author 1m.dm
 * @copyright Energine 2006
 * @version 1.0.0
 */
namespace Energine\share\components;

use Energine\share\gears\Builder;
use Energine\share\gears\ComponentConfig;
use Energine\share\gears\DBWorker;
use Energine\share\gears\Document;
use Energine\share\gears\IBlock;
use Energine\share\gears\Object;
use Energine\share\gears\Request;
use Energine\share\gears\SystemException;

/**
 * Builder interface.
 * @code
interface IBuilder;
 * @endcode
 */
interface IBuilder {
    /**
     * Get build result.
     * @return mixed
     */
    public function getResult();

    /**
     * Run building.
     * @return mixed
     */
    public function build();
}

/**
 * Page component.
 * @method string translate() translate(string $string, int $langID = null)
 * @method string dateToString() dateToString(int $year, int $month, int $day)
 * @code
class Component;
 * @endcode
 */
class Component extends Object implements IBlock {
    use DBWorker;
    /**
     * Default state name:
     * @code
     * 'main'
     * @endcode
     * @var string DEFAULT_STATE_NAME
     */
    const DEFAULT_STATE_NAME = 'main';
    /**
     * Page document.
     * @var Document $document
     */
    public $document;
    /**
     * Document DOM of the component.
     * @var \DOMDocument $doc
     */
    protected $doc;
    /**
     * Instance of the Request object.
     * @var Request $request
     */
    protected $request;
    /**
     * Component parameters.
     * @var array $params
     */
    protected $params;
    /**
     * Module name that owns the component.
     * @var string $module
     */
    protected $module;

    /**
     * Response object exemplar.
     * @var \Energine\share\gears\Response $response
     */
    protected $response;
    /**
     * Builder of the component result.
     * @var IBuilder $builder
     */
    protected $builder = null;
    /**
     * Component configurations.
     * @var ComponentConfig $config
     */
    protected $config;
    /**
     * Rights level required for running component's method.
     * @var int $rights
     */
    private $rights;
    /**
     * Component name.
     * @var string $name
     */
    private $name;
    /**
     * Indicator, that indicates whether the component is active.
     * @var boolean $enabled
     */
    private $enabled = true;
    /**
     * State parameters.
     * @var array $stateParams
     */
    private $stateParams = false;
    /**
     * Component properties.
     * @var array $properties
     */
    private $properties = [];
    /**
     * Name of the current component state.
     * @var string $state
     */
    private $state = self::DEFAULT_STATE_NAME;

    /**
     * @param string $name Component name.
     * @param array $params Component parameters.
     */


    public function __construct($name, array $params = null) {
        list(, $this->module) = explode('\\', get_called_class());

        $this->name = $name;
        $this->document = E()->getDocument();
        $this->params = $this->defineParams();
        if (is_array($params)) {
            foreach ($params as $name => $value) {
                $this->setParam($name, $value);
            }
        }
        $this->rights = $this->getParam('rights');
        $this->response = E()->getResponse();
        $this->request = E()->getRequest();

        $this->doc = new \DOMDocument('1.0', 'UTF-8');
        $this->document->componentManager->register($this);
        $this->setProperty('template',
            $template = $this->request->getPath(Request::PATH_TEMPLATE, true));
        $this->setProperty(
            'single_template',
            ($this->document->getProperty('single') ? $template :
                $template . 'single/' . $this->getName() . '/')
        );

        $this->determineState();
        //Определяем sample
        $ifs = class_implements($this);
        if (!empty($ifs)) {
            foreach ($ifs as $iname) {
                $iname = simplifyClassName($iname);
                if (strtolower(substr($iname, 0, 6)) == 'sample') {
                    $this->setProperty('sample', substr($iname, 6));
                    break;
                }
            }
        }
    }

    /**
     * Defines allowable component parameters and their default values as an array like <tt>array(paramName => defaultValue)</tt>
     * @return array
     */
    protected function defineParams() {
        return [
            'state'  => $this->state,
            'rights' => $this->document->getRights(),
            'config' => false,
            'active' => false,
        ];
    }

    /**
     * Set component parameter if such exist.
     * If this parameter is not exist SystemException will be generated.
     * @param string $name Parameter name.
     * @param mixed $value parameter value.
     * @throws SystemException 'ERR_DEV_NO_PARAM'
     */
    protected function setParam($name, $value) {
        if (!array_key_exists($name, $this->params)) {
            throw new SystemException('ERR_DEV_NO_PARAM', SystemException::ERR_DEVELOPER, $name);
        }
        if ($name == 'active') {
            $value = (bool)$value;
        }
        if (is_scalar($value)) {
            $value = explode('|', $value);
            $this->params[$name] =
                (sizeof($value) == 1) ? current($value) : $value;
        } elseif (is_array($value)) {
            $this->params[$name] = $value;
        } else {
            $this->params[$name] = $value;
        }
    }

    /**
     * Get component parameter.
     * If such parameter is not exist @b @c null will be returned.
     * @param string $name Parameter name.
     * @return mixed
     * @final
     */
    final protected function getParam($name) {
        return (array_key_exists($name, $this->params) ? $this->params[$name] : null);
    }

    /**
     * Set/update property value.
     * @param string $propName Property name.
     * @param mixed $propValue Property value.
     * @final
     */
    final protected function setProperty($propName, $propValue) {
        $this->properties[$propName] = $propValue;
    }

    /**
     * @copydoc IBlock::getName
     * @final
     */
    final public function getName() {
        return $this->name;
    }

    /**
     * Determine current state.
     * @todo Если компонент активный - то передача значения в параметре state - ни на что не влияет,
     * @todo все равно используется состояние определяемое конфигом
     * @todo непонятно то ли это фича то ли бага
     * @final
     */
    final private function determineState() {
        //Текущее действие берем из параметров
        //По умолчанию оно равно self::DEFAULT_STATE_NAME
        $this->state = $this->getParam('state');

        // если это основной компонент страницы, должен быть конфигурационный файл
        if ($this->isActive()) {
            if ($this->getConfig()->isEmpty()) {
                throw new SystemException('ERR_DEV_NO_COMPONENT_CONFIG', SystemException::ERR_DEVELOPER,
                    $this->getName());
            }

            // определяем действие по запрошенному URI
            $action =
                $this->getConfig()->getActionByURI($this->request->getPath(Request::PATH_ACTION, true));
            if ($action !== false) {
                $this->state = $action['name'];
                $this->stateParams = $action['params'];
            }

        } // если имя действия указано в POST-запросе - используем его
        elseif (isset($_POST[$this->getName()]['state'])) {
            $this->state = $_POST[$this->getName()]['state'];
        }

        // устанавливаем права на действие из конфигурации, если определены
        if (!$this->getConfig()->isEmpty()) {
            $this->getConfig()->setCurrentState($this->getState());
            $sc = $this->getConfig()->getCurrentStateConfig();

            if (isset($sc['rights'])) {
                $this->rights = (int)$sc['rights'];
            }

            if ($csp = $this->getConfig()->getCurrentStateParams()) {
                if ($this->stateParams) {
                    $this->stateParams = array_merge($this->stateParams, $csp);
                } else {
                    $this->stateParams = $csp;
                }
            }
        }

    }

    /**
     * Get the @c 'active' parameter of the component.
     * @return bool
     * @final
     */
    final protected function isActive() {
        return $this->params['active'];
    }

    /**
     * Get component configurations.
     * @return ComponentConfig
     * @note This method was created for redefining configurations in the children.
     * @final
     */
    protected function getConfig() {
        if (!$this->config) {
            $this->config = new ComponentConfig($this->getParam('config'), get_class($this), $this->module);
        }

        return $this->config;
    }

    /**
     * Get current state of the component.
     * @return string
     * @final
     */
    final public function getState() {
        return $this->state;
    }

    /**
     * Run current state method
     * @throws SystemException
     */
    public function run() {
        if (!$params = $this->getStateParams()) {
            $params = [];
        }

        if (method_exists($this, $this->getState() . 'State')) {
            call_user_func_array([$this, $this->getState() . 'State'], $params);
        } elseif (method_exists($this, $this->getState())) {
            call_user_func_array([$this, $this->getState()], $params);
        } elseif (is_callable([$this, $this->getState(). 'State'])) {
            call_user_func_array([$this, $this->getState() . 'State'], $params);
        } elseif (is_callable([$this, $this->getState()])) {
            call_user_func_array([$this, $this->getState()], $params);
        }
        else {
            throw new SystemException(
                'ERR_DEV_NO_ACTION',
                SystemException::ERR_DEVELOPER,
                [$this->getState(), $this->getName()]
            );
        }
    }

    /**
     * Get state parameters.
     * @param bool $returnAsAssocArray Return as an associative?
     * @return array
     * @todo Тут какой то беспорядок, то false то пустой array
     */
    public function getStateParams($returnAsAssocArray = false) {
        if (!$returnAsAssocArray && ($this->stateParams !== false)) {
            return array_values($this->stateParams);
        }

        return $this->stateParams;
    }

    /**
     * @copydoc IBlock::enabled
     * @final
     */
    final public function enabled() {
        return $this->enabled;
    }

    /**
     * @return \DOMDocument|\DOMElement
     */
    public function build() {
        $result = $this->doc->createElement('component');
        $result->setAttribute('name', $this->getName());
        $result->setAttribute('componentAction', $this->getState());
        $result->setAttribute('class', simplifyClassName(get_class($this)));

        foreach ($this->properties as $propName => $propValue) {
            $result->setAttribute($propName, $propValue);
        }

        /*
        * Существует ли построитель и правильно ли он отработал?
        * Построитель может не существовать, если мы создаем компонент в котором нет данных.
        */
        if ($this->getBuilder() && $this->getBuilder()->build()) {
            $builderResult = $this->getBuilder()->getResult();
            if ($builderResult instanceof \DOMNode) {
                $result->appendChild(
                    $this->doc->importNode(
                        $builderResult,
                        true
                    )
                );
            }
            elseif($builderResult instanceof \DOMNodeList){
                foreach($builderResult as $node){
                    $result->appendChild(
                        $this->doc->importNode(
                            $node,
                            true
                        )
                    );
                }
            }
            else {
                $el = $this->doc->createElement('result', $builderResult);
                $el->setAttribute('xml:id', 'result');
                $result->appendChild($el);
            }
        }
        $this->doc->appendChild($result);
        $result = $this->doc;

        return $result;
    }

    /**
     * Get component builder.
     * @return Builder
     * @final
     */
    final public function getBuilder() {
        return $this->builder;
    }

    /**
     * Set component builder.
     * @param IBuilder $builder Builder.
     * @final
     */
    final protected function setBuilder(IBuilder $builder) {
        $this->builder = $builder;
    }

    /**
     * @copydoc IBlock::getCurrentStateRights
     * @final
     */
    final public function getCurrentStateRights() {
        return (int)$this->rights;
    }

    /**
     * Create component from XML description.
     * @param \SimpleXMLElement $componentDescription Component description.
     * @param array $externalParams additional params
     * @return Component
     * @throws SystemException ERR_DEV_NO_REQUIRED_ATTRIB [attribute_name]
     */
    static public function createFromDescription(\SimpleXMLElement $componentDescription, $externalParams = []) {
        // перечень необходимых атрибутов компонента
        $requiredAttributes = [
            'name', /*'module', */
            'class'
        ];

        $name = $class = $module = null;
        //после отработки итератора должны получить $name, $module, $class
        foreach ($requiredAttributes as $attrName) {
            if (!isset($componentDescription[$attrName])) {
                throw new SystemException("ERR_DEV_NO_REQUIRED_ATTRIB $attrName", SystemException::ERR_DEVELOPER);
            }
            $$attrName = (string)$componentDescription[$attrName];
        }


        // извлекаем параметры компонента
        $params = null;
        if (isset($componentDescription->params)) {
            $params = [];
            foreach ($componentDescription->params->param as $tagName => $paramDescr) {
                /**
                 * @var \SimpleXMLElement $paramDescr
                 */
                if ($tagName == 'param') {
                    if (isset($paramDescr['name'])) {
                        $paramName = (string)$paramDescr['name'];
                        //Если count больше ноля значит это вложенный SimpleXML елемент
                        if (!$paramDescr->count()) {
                            $paramValue = (string)$paramDescr;
                        } else {
                            list($paramValue) = $paramDescr->children();
                        }

                        //$paramValue = (string) $paramDescr;

                        //Если в массиве параметров уже существует параметр с таким именем, превращаем этот параметр в массив
                        if (isset($params[$paramName])) {
                            if (!is_array($params[$paramName])) {
                                $params[$paramName] =
                                    [$params[$paramName]];
                            }
                            array_push($params[$paramName], $paramValue);
                        } else {
                            $params[$paramName] = $paramValue;
                        }
                    }
                }
            }
        }
        if(is_array($params)){
            $params = array_merge($externalParams, $params);
        }

        $result = self::create($name, $class, $params);

        return $result;
    }

    /**
     * Create component by requested parameters.
     * @param string $name Component name.
     * @param string $fqClassName Component class name.
     * @param array $params Parameters.
     * @return Component
     * @todo Na huy Na huy
     * @throws SystemException
     */
    static public function create($name, $fqClassName, $params = null) {
        try {
            $result = new $fqClassName($name, $params);
        } catch (SystemException $e) {
            throw new SystemException($e->getMessage(), SystemException::ERR_DEVELOPER, [
                'class' => $fqClassName,
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $result;
    }

    /**
     * Disable component.
     * @final
     */
    final public function disable() {
        $this->enabled = false;
    }

    /**
     * Enable component.
     * @final
     */
    final public function enable() {
        $this->enabled = true;
    }

    /**
     * Set state parameter.
     * Usually this is required by dynamic component creation and assigning him state parameter from other component.
     * @param string $paramName Parameter name.
     * @param mixed $paramValue Parameter value.
     */
    public function setStateParam($paramName, $paramValue) {
        $this->stateParams[$paramName] = $paramValue;
    }

    /**
     * Default action.
     * @return boolean
     */
    protected function main() {
        $this->prepare(); // вызываем метод подготовки данных
        return true;
    }

    /**
     * Prepare data.
     * @note It calls at the beginning of the method, that realize main action.
     */
    protected function prepare() {
    }

    /**
     * Get property value.
     * @param string $propName
     * @return mixed
     * @final
     */
    final protected function getProperty($propName) {
        $result = false;
        if (isset($this->properties[$propName])) {
            $result = $this->properties[$propName];
        }

        return $result;
    }

    /**
     * Remove property.
     * @param string $propName Property name.
     * @final
     */
    final protected function removeProperty($propName) {
        unset($this->properties[$propName]);
    }
}

