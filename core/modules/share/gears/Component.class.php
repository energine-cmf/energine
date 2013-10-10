<?php
/**
 * Класс Component.
 *
 * @package energine
 * @subpackage kernel
 * @author 1m.dm
 * @copyright Energine 2006
 */


/**
 * Компонент страницы.
 *
 * @package energine
 * @subpackage kernel
 * @author 1m.dm
 */
class Component extends DBWorker implements IBlock {
    /**
     * Имя состояния по-умолчанию.
     */
    const DEFAULT_STATE_NAME = 'main';
    /**
     * @access protected
     * @var DOMDocument DOM-документ компонента
     */
    protected $doc;

    /**
     * @access protected
     * @var Request экземпляр объекта Request
     */
    protected $request;

    /**
     * @access protected
     * @var array параметры компонента
     */
    protected $params;

    /**
     * @access public
     * @var Document документ страницы
     */
    public $document;

    /**
     * @access protected
     * @var string имя модуля, которому принадлежит компонент
     */
    protected $module;

    /**
     * @access protected
     * @var Response экземпляр объекта Response
     */
    protected $response;

    /**
     * @access private
     * @var int уровень прав, необходимый для запуска метода компонента
     */
    private $rights;

    /**
     * @access private
     * @var string имя компонента
     */
    private $name;

    /**
     * @var boolean Флаг, указывающий на то, является ли компонент активным
     * @access private
     */

    private $enabled = true;
    /**
     * @access private
     * @var array параметры состояния
     */
    private $stateParams = false;

    /**
     * @access private
     * @var array свойства компонента
     */
    private $properties = array();

    /**
     * @access private
     * @var array список ошибок, произошедших во время работы компонента
     */
    private $errors = array();

    /**
     * Результат является объектом класса DOMNode или boolean:
     * true - компонент отработал успешно, но ничего не вывел;
     * false - произошла ошибка при работе компонента.
     *
     * @access private
     * @var mixed результат работы компонента
     */
    //private $result;

    /**
     * @access private
     * @var string имя текущего состояния компонента
     */
    private $state = self::DEFAULT_STATE_NAME;

    /**
     * @access protected
     * @var AbstractBuilder построитель результата работы компонента
     */
    protected $builder = false;

    /**
     * @access protected
     * @var ComponentConfig конфигурация компонента
     */
    protected $config;

    /**
     * Конструктор класса.
     *
     * @access public
     * @param string $name
     * @param string $module
     * @param array $params
     * @return void
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct();

        $this->name = $name;
        $this->module = $module;
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
        /**
         * @todo Проверить, можно ли перенести в build
         */
        $this->doc = new DOMDocument('1.0', 'UTF-8');
        $this->document->componentManager->register($this);
        $this->setProperty('template',
            $template = $this->request->getPath(Request::PATH_TEMPLATE, true));
        $this->setProperty(
            'single_template',
            ($this->document->getProperty('single') ? $template :
                $template . 'single/' . $this->getName() . '/')
        );
        //$this->config = new ComponentConfig($this->getParam('config'), get_class($this), $this->module);
        $this->determineState();
        //Определяем sample
        $ifs = class_implements($this);

        if (!empty($ifs)) {
            foreach ($ifs as $iname){
                if(strtolower(substr($iname, 0, 6)) == 'sample'){
                    $this->setProperty('sample', substr($iname, 6));
                    break;
                }
            }
        }
    }

    /**
     * Возвращает флаг активности компонента
     *
     * @return bool
     * @access protected
     * @final
     */

    final protected function isActive() {
        return $this->params['active'];
    }

    /**
     * Возвращает конфиг компонента
     * Метод введен для возможности переопределения конфига в потомках
     *
     * @return ComponentConfig
     */
    protected function getConfig() {
        if (!$this->config) {
            $this->config = new ComponentConfig($this->getParam('config'), get_class($this), $this->module);
        }
        return $this->config;
    }

    /**
     * Устанавливает построитель компонента.
     *
     * @access protected
     * @final
     * @param IBuilder $builder
     * @return void
     */
    final protected function setBuilder(IBuilder $builder) {
        $this->builder = $builder;
    }

    /**
     * Возвращает построитель компонента.
     *
     * @access protected
     * @final
     * @return AbstractBuilder
     */
    final protected function getBuilder() {
        return $this->builder;
    }

    /**
     * Определяет допустимые параметры компонента и их значения по-умолчанию
     * в виде массива array(paramName => defaultValue).
     *
     * @access protected
     * @return array
     */
    protected function defineParams() {
        return array(
            'state' => $this->state,
            'rights' => $this->document->getRights(),
            'config' => false,
            'active' => false,
        );
    }

    /**
     * Устанавливает значение параметра компонента, если такой существует.
     * В противном случае возбуждается исключение.
     *
     * @access protected
     * @param string $name
     * @param mixed $value
     * @return void
     */
    protected function setParam($name, $value) {
        if (!isset($this->params[$name])) {
            throw new SystemException('ERR_DEV_NO_PARAM', SystemException::ERR_DEVELOPER, $name);
        }
        if ($name == 'active') {
            $value = (bool)$value;
        }
        /*if (in_array($name, array('state','configFilename', 'active'))) {
            throw new SystemException('ERR_DEV_INVARIANT_PARAM', SystemException::ERR_DEVELOPER, $name);
        }*/

        // если новое значение пустое - оставляем значение по-умолчанию
        if (!empty($value) || $value === false) {
            if (is_scalar($value)) {
                //ОБрабатываем случай передачи массива-строки в параметры
                $value = explode('|', $value);
                $this->params[$name] =
                    (sizeof($value) == 1) ? current($value) : $value;
            } elseif (is_array($value)) {
                //$this->params[$name] = array_values($value);
                $this->params[$name] = $value;
            } else {
                $this->params[$name] = $value;
            }
        }
    }

    /**
     * Возвращает значение параметра компонента, или null, если такого
     * параметра не существует.
     *
     * @access protected
     * @final
     * @param string $name
     * @return mixed
     */
    final protected function getParam($name) {
        return (isset($this->params[$name]) ? $this->params[$name] : null);
    }

    /**
     * Определяет текущее действие
     *
     * @todo Если компонент активный - то передача значения в параметре state - ни на что не влияет,
     * @todo все равно используется состояние определяемое конфигом
     * @todo непонятно то ли это фича то ли бага
     *
     * @return void
     * @access private
     * @final
     */
    final private function determineState() {
        //Текущее действие берем из параметров
        //По умолчанию оно равно self::DEFAULT_STATE_NAME
        $this->state = $this->getParam('state');

        // если это основной компонент страницы, должен быть конфигурационный файл
        if ($this->isActive()) {
            if ($this->getConfig()->isEmpty()) {
                throw new SystemException('ERR_DEV_NO_COMPONENT_CONFIG', SystemException::ERR_DEVELOPER, $this->getName());
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
                if($this->stateParams){
                    $this->stateParams = array_merge($this->stateParams, $csp);
                }
                else {
                    $this->stateParams = $csp;
                }
            }
        }

    }

    /**
     * Определяет имя текущего состояния компонента.
     *
     * @access public
     * @return string
     * @final
     */
    final public function getState() {
        return $this->state;
    }

    /**
     * Возвращает уровень прав пользователя, необходимых для запуска
     * текущего действия компонента.
     *
     * @access public
     * @final
     * @return int
     */
    final public function getCurrentStateRights() {
        return (int)$this->rights;
    }

    /**
     * Возвращает имя компонента.
     *
     * @access public
     * @final
     * @return string
     */
    final public function getName() {
        return $this->name;
    }

    /**
     * Запускает компонент на исполнение.
     *
     * @access public
     * @final
     * @return void
     */
    public function run() {
        if (!method_exists($this, $this->getState())) {
            throw new SystemException(
                'ERR_DEV_NO_ACTION',
                SystemException::ERR_DEVELOPER,
                array($this->getState(), $this->getName())
            );
        }
        $params = $this->getStateParams();
        if (empty($params)) {
            $this->{$this->getState()}();
        } else {
            call_user_func_array(array($this, $this->getState()), $params);
        }

    }

    /**
     * Действие по-умолчанию.
     *
     * @access protected
     * @return boolean
     */
    protected function main() {
        $this->prepare(); // вызываем метод подготовки данных
        return true;
    }

    /**
     * Метод подготовки данных.
     * Вызывается вначале работы метода, реализующего основное действие.
     *
     * @access protected
     * @return void
     */
    protected function prepare() {
    }

    /**
     * Отключает отображение компонента
     *
     * @return void
     * @access public
     * @final
     */

    final public function disable() {
        $this->enabled = false;
    }

    /**
     * Включает отображение компонента
     *
     * @return void
     * @access public
     * @final
     */

    final public function enable() {
        $this->enabled = true;
    }

    /**
     * Возвращает активность компонента
     *
     * @return boolean
     * @access public
     * @final
     */

    final public function enabled() {
        return $this->enabled;
    }

    /**
     * Устанавливает значение свойства компонента.
     *
     * @access protected
     * @final
     * @param string $propName
     * @param mixed $propValue
     * @return void
     */
    final protected function setProperty($propName, $propValue) {
        $this->properties[$propName] = $propValue;
    }

    /**
     * Возвращает значение свойства компонента.
     *
     * @access protected
     * @final
     * @param string $propName
     * @return mixed
     */
    final protected function getProperty($propName) {
        $result = false;
        if (isset($this->properties[$propName])) {
            $result = $this->properties[$propName];
        }
        return $result;
    }

    /**
     * Удаляет свойство компонента.
     *
     * @access protected
     * @final
     * @param string
     * @return void
     */
    final protected function removeProperty($propName) {
        unset($this->properties[$propName]);
    }

    /**
     * Строит результат работы компонента используя определённый построитель.
     *
     * @access public
     * @return DOMDocument
     */
    public function build() {
        $result = $this->doc->createElement('component');
        $result->setAttribute('name', $this->getName());
        $result->setAttribute('module', $this->module);
        $result->setAttribute('componentAction', $this->getState());
        $result->setAttribute('class', get_class($this));

        foreach ($this->properties as $propName => $propValue) {
            $result->setAttribute($propName, $propValue);
        }

        /*
        * Существует ли построитель и правильно ли он отработал?
        * Построитель может не существовать, если мы создаем компонент в котором нет данных.
        */
        if ($this->getBuilder() && $this->getBuilder()->build()) {
            $builderResult = $this->getBuilder()->getResult();
            if ($builderResult instanceof DOMNode) {
                $result->appendChild(
                    $this->doc->importNode(
                        $builderResult,
                        true
                    )
                );
            } else {
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
     * Возвращает параметры состояния.
     *
     * @param bool  - возвращает ассоциативный/обычный массив
     * @access public
     * @return array
     *
     * @todo Тут какой то беспорядок, то false то пустой array
     */
    public function getStateParams($returnAsAssocArray = false) {
        if (!$returnAsAssocArray && ($this->stateParams !== false)) {
            return array_values($this->stateParams);
        }

        return $this->stateParams;

    }

    /**
     * Устанавливает параметр состояния
     * Обычно такое требуется при динамическом создании компонента и передаче ему параметров состояния из другого компонента
     *
     * @param  $paramName string
     * @param  $paramValue mixed
     * @return void
     */
    public function setStateParam($paramName, $paramValue) {
        $this->stateParams[$paramName] = $paramValue;
    }

}


/**
 * Class IBuilder
 */
interface IBuilder {
    /**
     * @return mixed
     */
    public function getResult();

    /**
     * @return mixed
     */
    public function build();
}

