<?php

/**
 * Класс ComponentManager.
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @copyright Energine 2006
 * @version $Id$
 */

//require_once('core/framework/SystemConfig.class.php');

/**
 * Менеджер набора компонентов документа.
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @final
 */
final class ComponentManager extends Object {

    /**
     * @access private
     * @var array набор компонентов
     */
    private $components = array();

    /**
     * @access private
     * @var Document документ
     */
    private $document;

    /**
     * Конструктор класса.
     *
     * @access public
     * @param Document $document
     * @return void
     */
    public function __construct(Document $document) {
        parent::__construct();

        $this->document = $document;
    }


    /**
	 * Добавляет компонент.
	 *
	 * @access public
	 * @param Component $component
	 * @param string имя файла шаблона в котором находится компонент
	 * @return void
	 */
    public function addComponent(Component $component, $fileName = false) {
        $this->components[$component->getName()] = array(
        'component' => $component,
        'file' => $fileName
        );
    }

    /**
     * Возвращает компонент с указанным именем.
     *
     * @access public
     * @param string $name имя компонента
     * @return Component
     */
    public function getComponentByName($name) {
        $result = false;
        if (isset($this->components[$name])) {
            $result = $this->components[$name]['component'];
        }
        return $result;
    }

    /**
     * Возвращает набор компонентов по имени класса.
     *
     * @access public
     * @param string $className имя класса
     * @return array
     */
    public function getComponentsByClassName($className) {
        $result = array();
        foreach ($this->components as $componentName => $component) {
            if (get_class($component['component']) == $className) {
                $result[$componentName] = $component['component'];
            }
        }

        return $result;
    }

    /**
     * Загружает описания компонентов из файла шаблона(layout или content)
     *
     * @param string имя файла content'а или layout'а
     * @param string имя компонента который нужно загрузить
     * @return bool Возвращает флаг указівающий на то загружены ли компоненты
     * @access public
     */

    public function loadComponentsFromFile($fileName, $onlyComponent = false) {
        $result = false;
        //проверяем существует ли такой файл
        if (!file_exists($fileName)) {
            throw new SystemException('ERR_DEV_NO_TEMPLATE_FILE', SystemException::ERR_CRITICAL, $fileName);
        }
        //и можно ли из него загрузить данные
        if (!($file = simplexml_load_file($fileName))) {
            throw new SystemException('ERR_DEV_BAD_TEMPLATE_FILE', SystemException::ERR_CRITICAL, $fileName);
        }
        if ($onlyComponent) {
            $components = $file->xpath("/*/component[@name='".$onlyComponent."']");
        }
        else {
            $components = $file->xpath('/*/component');
        }

        if (!empty($components)) {
            $result = true;
            foreach ($components as $componentDescription) {
                $this->addComponent($this->createComponentFromXML($componentDescription), $fileName);
            }
        }

        return $result;

    }

    /**
     * Создание компонента из XML описания
     *
     * @param SimpleXMLElement описание компонента
     * @return Component
     * @access public
     */

    public function createComponentFromXML(SimpleXMLElement $componentDescription) {
        // перечень необходимых атрибутов компонента
        $requiredAttributes = array('name', 'module', 'class');

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
            $params = array();
            foreach ($componentDescription->params->param as $tagName => $paramDescr) {
                if ($tagName == 'param') {
                    if (isset($paramDescr['name'])) {
                        $paramName = (string)$paramDescr['name'];
                        $paramValue = (string)$paramDescr;

                        //Если в массиве параметров уже существует параметр с таким именем, превращаем этот параметр в массив
                        if (isset($params[$paramName])) {
                            if (!is_array($params[$paramName])) {
                            	$params[$paramName] = array($params[$paramName]);
                            }
                            array_push($params[$paramName], $paramValue);
                        }
                        else {
                            $params[$paramName] = $paramValue;
                        }
                    }
                }
            }
        }

        $component = $this->createComponent($name, $module, $class, $params);

        return $component;
    }

    /**
     * Создает компонент.
     *
     * @access public
     * @param string $name
     * @param string $module
     * @param string $class
     * @param array $params
     * @return Component
     */
    public function createComponent($name, $module, $class, $params = null) {
        return new $class($name, $module, $this->document, $params);
    }
    /**
     * Возвращает набор компонентов
     *
     * @return array
     * @access public
     */

    public function getComponents() {
        return $this->components;
    }

}
