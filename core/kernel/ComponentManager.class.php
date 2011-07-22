<?php

/**
 * Содержит класс ComponentManager и интерфейс Block
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @copyright Energine 2006
 */


/**
 * Менеджер набора компонентов документа.
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @final
 */
final class ComponentManager extends Object implements Iterator {

    /**
     * Массив компонентов
     * используется для быстрого поиска компонента функцией getComponentByName
     * Наполняется  при добавлении компонента в поток
     *
     * @access private
     * @var array набор компонентов
     */
    private $registeredBlocks = array();

    /**
     * @access private
     * @var Document документ
     * @static
     */
    static private $document;

    /**
     * Содержит как компоненты так и контейнеры
     * @var IBlock[] Массив блоков
     */
    private $blocks = array();
    /**
     * Массив имен блоков
     * заполняется в функции rewind
     * используется для ускорения итерации
     * @var array
     */
    private $blockNames = array();
    /**
     * Текщий индекс итерации
     * @var int
     */
    private $iteratorIndex = 0;

    /**
     * Конструктор класса.
     *
     * @access public

     * @return void
     */
    public function __construct(Document $document) {
        self::$document = $document;
    }

    public function register(IBlock $block) {
        $this->registeredBlocks[$block->getName()]  = $block;
    }

    /**
     * Добавляет блок в список блоков
     * @param IBlock $block
     * @return void
     */
    public function add(IBlock $block) {
        $this->blocks[$block->getName()] = $block;
/*
        $iterateContainer = function(Block $block) use(&$iterateContainer) {
            $result = array();
            if ($block instanceof ComponentContainer) {
                foreach ($block as $blockChildName => $blockChild) {
                    $result[$blockChildName] = $blockChild;
                    $result = array_merge($result, $iterateContainer($blockChild));
                }
            }
            else {
                 $result[$block->getName()] = $block;
            }
            return $result;
        };

        $this->blockCache = array_merge($this->blockCache, $iterateContainer($block));
 *
 */
    }


    /**
     * Добавляет компонент.
     *
     * @access public
     * @param Component $component
     * @return void
     * @deprecated С поялением концепции блоков нужно использовать ComponentManager::add
     */
    public function addComponent(Component $component) {
        $this->add($component);
    }

    /**
     * Возвращает блок с указанным именем.
     *
     * @access public
     * @param string $name имя компонента
     * @return Component
     */
    public function getBlockByName($name) {
        $result = false;
        if (isset($this->registeredBlocks[$name])) {
            $result = $this->registeredBlocks[$name];
        }
        return $result;
    }

    /**
     * Создание компонента из XML описания
     *
     * @param SimpleXMLElement описание компонента
     * @return Object
     * @access public
     * @static
     */

    static public function createComponentFromDescription(SimpleXMLElement $componentDescription) {
        // перечень необходимых атрибутов компонента
        $requiredAttributes = array('name', 'module', 'class');

        $name = $class = $module = null;
        //после отработки итератора должны получить $name, $module, $class
        foreach ($requiredAttributes as $attrName) {
            if (!isset($componentDescription[$attrName])) {
                throw new SystemException("ERR_DEV_NO_REQUIRED_ATTRIB $attrName", SystemException::ERR_DEVELOPER);
            }
            $$attrName = (string) $componentDescription[$attrName];
        }


        // извлекаем параметры компонента
        $params = null;
        if (isset($componentDescription->params)) {
            $params = array();
            foreach ($componentDescription->params->param as $tagName => $paramDescr) {
                if ($tagName == 'param') {
                    if (isset($paramDescr['name'])) {
                        $paramName = (string) $paramDescr['name'];
                        //Если count больше ноля значит это вложенный SimpleXML елемент
                        $paramValue = (!$paramDescr->count())?(string) $paramDescr:$paramDescr->children();
                        //$paramValue = (string) $paramDescr;

                        //Если в массиве параметров уже существует параметр с таким именем, превращаем этот параметр в массив
                        if (isset($params[$paramName])) {
                            if (!is_array($params[$paramName])) {
                                $params[$paramName] =
                                        array($params[$paramName]);
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
/*        $result = false;
        if(
            !isset($params['rights'])
            ||
            (isset($params['rights']) && self::$document->getRights() >= $params['rights'])
        ) {
            $result = self::_createComponent($name, $module, $class, $params);
        }*/

        $result = self::_createComponent($name, $module, $class, $params);

        return $result;
    }

    /**
     * Создает компонент.
     * Использует
     *
     * @access public
     * @param string $name
     * @param string $module
     * @param string $class
     * @param array $params
     * @return Component
     */
    public function createComponent($name, $module, $class, $params = null) {
        return call_user_func_array(array('ComponentManager', '_createComponent'), func_get_args());
    }

    /**
     * Осуществляет поиск блока в описании
     *
     * @static
     * @param SimpleXMLElement $containerXMLDescription
     * @param  $blockName
     * @return IBlock|bool
     */
    static public function findBlockByName(SimpleXMLElement $containerXMLDescription, $blockName) {
        $blocks = $containerXMLDescription->xpath(
            'descendant-or-self::*[name()="container" or name() = "component"]' .
                    '[@name="' . $blockName . '"]'
        );
        if (!empty($blocks)) {
            list($blocks) = $blocks;
        }
        else {
            $blocks = false;
        }

        return $blocks;
    }

    /**
     * Осуществляет загрузку описания блока из файла
     *
     * @static
     * @throws SystemException
     * @param  $blockDescriptionFileName
     * @return SimpleXMLElement
     */
    static public function getDescriptionFromFile($blockDescriptionFileName) {
        if (!file_exists($blockDescriptionFileName)) {
            throw new SystemException('ERR_DEV_NO_CONTAINER_FILE', SystemException::ERR_CRITICAL, $blockDescriptionFileName);
        }
        if (!(
        $blockDescription = simplexml_load_file($blockDescriptionFileName))) {
            throw new SystemException('ERR_DEV_BAD_CONTAINER_FILE', SystemException::ERR_CRITICAL, $blockDescriptionFileName);
        }

        return $blockDescription;
    }

    /**
     * Создает блок по его описанию
     *
     * @static
     * @throws SystemException
     * @param SimpleXMLElement $blockDescription
     * @return IBlock
     */
    static public function createBlockFromDescription(SimpleXMLElement $blockDescription) {
        $result = false;
        switch ($blockDescription->getName()) {
            case 'content':
                $result =
                        ComponentContainer::createFromDescription($blockDescription, array('tag' => 'content'));
                break;
            case 'page':
                $result =
                        ComponentContainer::createFromDescription($blockDescription, array('tag' => 'layout'));
                break;
            case 'container':
                $result =
                        ComponentContainer::createFromDescription($blockDescription);
                break;
            case 'component':
                $result =
                        self::createComponentFromDescription($blockDescription);
                break;
            default:
                throw new SystemException('ERR_UNKNOWN_BLOCKTYPE', SystemException::ERR_CRITICAL);
                break;
        }

        return $result;
    }

    /**
     * Создает компонент по переданнім параметрам
     *
     * @static
     * @throws SystemException
     * @param  $name
     * @param  $module
     * @param  $class
     * @param  $params
     * @return
     */
    static private function _createComponent($name, $module, $class, $params = null) {
        try {
            $result = new $class($name, $module, $params);
        }
        catch (SystemException $e) {
            throw new SystemException($e->getMessage(), SystemException::ERR_DEVELOPER, array(
                'class' => (($module !==
                        'site') ? str_replace('*', $module, CORE_COMPONENTS_DIR) :
                        SITE_COMPONENTS_DIR . $module) . '/' . $class .
                        '.class.php',
                'trace' => $e->getTraceAsString()
            ));
        }
        return $result;
    }

    /**
     * Загружает массив имен блоков в переменную blockNames
     *
     * @return void
     */
    public function rewind() {
        $this->blockNames = array_keys($this->blocks);
        $this->iteratorIndex = 0;
    }

    /**
     * @return boolean
     */
    public function valid() {
        return isset($this->blockNames[$this->iteratorIndex]);
    }

    /**
     * @return string
     */
    public function key() {
        return $this->blockNames[$this->iteratorIndex];
    }

    /**
     * @return void
     */
    public function next() {
        $this->iteratorIndex++;
    }

    /**
     * @return IBlock
     */
    public function current() {
        return $this->blocks[$this->blockNames[$this->iteratorIndex]];
    }
}

/**
 *
 */
interface IBlock {
    /**
     * @abstract
     * @return void
     */
    public function run();
    
    /**
     * @abstract
     * @return bool
     */
    public function enabled();
    /**
     * @abstract
     * @return void
     */
    public function getCurrentStateRights();

    /**
     * @abstract
     * @return DOMDocument
     */
    public function build();

    /**
     * @abstract
     * @return string
     */
    public function getName();
}