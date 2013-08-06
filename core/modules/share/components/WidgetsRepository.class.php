<?php
/**
 * Содержит класс WidgetsRepository
 *
 * @package energine
 * @subpackage share
 * @author spacelord
 * @copyright Energine 2010
 * @version $Id
 */


/**
 * Работа с виджетами (компонентов)
 *
 * @package energine
 * @subpackage share
 * @author spacelord
 */


class WidgetsRepository extends Grid {
    /**
     * @var Component
     *
     */
    private $tmpComponent;

    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module

     * @param array $params
     * @access public
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setTableName('share_widgets');
        $this->setOrder(array('widget_name' => QAL::ASC));
    }
    protected function createDataDescription(){
        $result = parent::createDataDescription();
        if(in_array($this->getState(), array('add', 'edit'))){
            $result->getFieldDescriptionByName('widget_xml')->setType(FieldDescription::FIELD_TYPE_CODE);
        }
        return $result;
    }

    /**
     * Постройка формы редактирования параметров компонента
     * Получает на вход XML данные виджета, на основании которых и строит форму
     *
     * @throws SystemException
     * @return void
     */
    protected function buildParamsForm() {
        if (!isset($_POST['modalBoxData'])) {
            throw new SystemException('ERR_INSUFFICIENT_DATA');
        }
        if (!$widgetXML = simplexml_load_string($_POST['modalBoxData'])) {
            throw new SystemException('ERR_BAD_XML_DESCR');
        }
        list($componentName) = $this->getStateParams();
        $component =
                ComponentManager::findBlockByName($widgetXML, $componentName);
        $dd = new DataDescription();
        $d = new Data();
        $this->setType(self::COMPONENT_TYPE_FORM_ALTER);
        $this->setDataDescription($dd);
        $this->setData($d);
        $this->setBuilder(new Builder());
        $this->js = $this->buildJS();
        foreach ($component->params->children() as $param) {
            $paramName = (string)$param['name'];

            $paramType = (isset($param['type'])) ? (string)$param['type'] : FieldDescription::FIELD_TYPE_STRING;

            $fd = new FieldDescription($paramName);
            if(isset($param['nullable'])){
                $fd->setProperty('nullable', true);
            }

            $fd->setType($paramType)->setProperty('tabName', $this->translate('TAB_PARAMS'));
            if (($paramType == FieldDescription::FIELD_TYPE_SELECT) && isset($param['values'])) {
                $availableValues = array();
                foreach (explode('|', (string)$param['values']) as $value) {
                    array_push($availableValues, array('key' => $value, 'value' => $value));
                }
                $fd->loadAvailableValues($availableValues, 'key', 'value');
            }
            $dd->addFieldDescription($fd);
            $f = new Field($paramName);
            $f->setRowData(0, $param);
            $d->addField($f);
        }
        $this->addToolbar($this->createToolbar());
    }

    /**
     * Создание виджета по переданному описанию
     *
     * @throws SystemException
     * @return void
     */
    public function buildWidget() {
        if (!isset($_POST['xml'])) {
            throw new SystemException('ERR_BAD_DATA');
        }
        $xml = $_POST['xml'];
        $xml = simplexml_load_string($xml);
        unset($_SERVER['HTTP_X_REQUEST']);
        $this->request->shiftPath(1);
        $this->tmpComponent =
                ComponentManager::createBlockFromDescription($xml);
        $this->tmpComponent->run();
    }

    /**
     * Для формы ввода имени для нового шаблона контента
     * отключаем получение данных
     * а какие там могут быть данные  - в самом то деле
     * @return bool|mixed
     */
    protected function loadData() {
        if ($this->getState() == 'showNewTemplateForm') return false;

        return parent::loadData();
    }

    /**
     * Для состояния buildWidget
     * возвращаем код динамического компонента
     * @return DOMDocument|void
     */
    public function build() {
        switch ($this->getState()) {
            case 'buildWidget':
                $result = $this->tmpComponent->build();
                break;
            default:
                $result = parent::build();
                break;
        }

        return $result;
    }

    /**
     * Сохраняет content для текущей страницы
     * @throws SystemException
     * @return void
     */
    protected function saveContent() {
        if (!isset($_POST['xml'])) {
            throw new SystemException('ERR_INSUFFICIENT_DATA');
        }
        $xml = $_POST['xml'];
        if (!simplexml_load_string($xml)) {
            throw new SystemException('ERR_BAD_XML');
        }
        $this->dbh->modify(QAL::UPDATE, 'share_sitemap', array('smap_content_xml' => $xml), array('smap_id' => E()->getDocument()->getID()));
        $b = new JSONCustomBuilder();
        $b->setProperties(array(
                               'xml' => $xml,
                               'result' => true,
                               'mode' => 'none'
                          ));
        $this->setBuilder($b);
    }

    /**
     * Выводит форму ввода имени нового шаблона
     * @return void
     */
    protected function showNewTemplateForm() {
        $this->setType(self::COMPONENT_TYPE_FORM_ADD);
        $this->prepare();
    }

    /**
     * Сохраняет расположение блоков
     * в текущем шаблоне
     *
     * если это шаблон из ядра
     * то создает новый одноименный шаблон
     * и переназначает его для текущей страницы
     * а также для всех страниц созданных по этому шаблону
     *
     * @throws SystemException
     * @return void
     */
    protected function saveTemplate() {
        if (!isset($_POST['xml'])) {
            throw new SystemException('ERR_INSUFFICIENT_DATA');
        }
        $xml = $_POST['xml'];
        if (!simplexml_load_string($xml)) {
            throw new SystemException('ERR_BAD_XML');
        }
        //Определяем шаблон текущей страницы
        $content = simplifyDBResult($this->dbh->select('share_sitemap', array('smap_content'), array('smap_id' => $this->document->getID())), 'smap_content', true);

        //если шаблон  - ядреный - мы не можем в него писать изменения
        if ($content == basename($content)) {
            //значит создаем одноименный шаблон в проекте
            file_put_contents(SITE_DIR . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . E()->getSiteManager()->getCurrentSite()->folder . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . $content, $xml);
            //создаем симлинк
            $symlink = self::createSymlink($content, E()->getSiteManager()->getCurrentSite()->folder);
            //переназначаем для данной страницы шаблон
            //перенезначаем для всех страниц созданных по ядреному шаблону
            $this->dbh->modify(QAL::UPDATE, 'share_sitemap', array('smap_content' => $symlink), array('smap_content' => $content));
        }
        else {
            //перезаписываем файл
            list($moduleName, $fileName) = array_values(pathinfo($content));

            file_put_contents(SITE_DIR . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . $fileName, $xml);
        }


        //формируем ответ
        $b = new JSONCustomBuilder();
        $b->setProperties(array(
                               'xml' => $xml,
                               'result' => true,
                               'mode' => 'none'
                          ));
        $this->setBuilder($b);
    }

    /**
     * Создает симлинк
     *
     * @static
     * @param $fileName имя файла
     * @param $module имя модуля
     * @return string
     */
    private static function createSymlink($fileName, $module) {
        if (!file_exists($dir = 'templates' . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . $module)) {
            //создаем ее
            mkdir($dir);
        }
        //Если уще существовал симлинк  - чтоб не морочиться  - просто удяляем
        if (file_exists($symlink = $dir . DIRECTORY_SEPARATOR . $fileName)) unlink($symlink);

        //создаем симлинк, не запускать же в самом деле сетап ради одной ссылки
        //как то так ../../../site/modules/[имя модуля]/templates/content/[имя файла]
        symlink(
            str_repeat('..' . DIRECTORY_SEPARATOR, 3) .
            'site' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR .
            $module . DIRECTORY_SEPARATOR . 'templates' .
            DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR .
            $fileName,

            $symlink
        );

        return $module . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * Создает новый шаблон
     *
     * @throws SystemException
     * @return void
     */
    protected function saveNewTemplate() {
        if (!isset($_POST['xml'])) {
            throw new SystemException('ERR_INSUFFICIENT_DATA');
        }
        $xml = $_POST['xml'];
        if (!simplexml_load_string($xml)) {
            throw new SystemException('ERR_BAD_XML');
        }
        $title = $_POST['title'];
        $contentFileName = ($contentName = Translit::asURLSegment($title)) . '.content.xml';

        //Создаем контентный файл
        file_put_contents(($target = 'site/modules/' . ($moduleName = E()->getSiteManager()->getCurrentSite()->folder) . '/templates/content/' . $contentFileName), $xml);

        $symlink = self::createSymlink($contentFileName, $moduleName);

        //изменяем шаблон страницы
        $this->dbh->modify(QAL::UPDATE, 'share_sitemap', array('smap_content_xml' => QAL::EMPTY_STRING, 'smap_content' => $symlink), array('smap_id' => $this->document->getID()));

        //вносим перевод, если не существует
        $ltagName = strtoupper('CONTENT_' . $contentName);

        if ($this->dbh->select('share_lang_tags', array('ltag_id'), array('ltag_name' => $ltagName)) === true) {
            $ltagID = $this->dbh->modify(QAL::INSERT, 'share_lang_tags', array('ltag_name' => $ltagName));
            foreach (array_keys(E()->getLanguage()->getLanguages()) as $langID) {
                $this->dbh->modify(QAL::INSERT, 'share_lang_tags_translation', array('lang_id' => $langID, 'ltag_value_rtf' => $title, 'ltag_id' => $ltagID));
            }
        }
        //формируем ответ
        $b = new JSONCustomBuilder();
        $b->setProperties(array(
                               'xml' => $xml,
                               'result' => true,
                               'mode' => 'none'
                          ));
        $this->setBuilder($b);
    }

    /**
     * Сбрасываем переопределения для контента и лейаута, возвращаясь к исходному шаблону
     * @throws SystemException
     */
    protected function revertTemplate() {
        $content = simplifyDBResult(
            $this->dbh->select(
                'SELECT  smap_content as content FROM share_sitemap WHERE smap_id = %s', $this->document->getID()),
            'content',
            true);
        if ((dirname($content) == '.') || !file_exists('templates/content/' . basename($content))) {
            throw new SystemException('ERR_CONTENT_NOT_REVERTED', SystemException::ERR_CRITICAL, $content);
        }
        $this->dbh->modify(QAL::UPDATE, 'share_sitemap', array('smap_content' => basename($content), 'smap_content_xml' => QAL::EMPTY_STRING), array('smap_content' => $content));
        $b = new JSONCustomBuilder();
        $b->setProperties(array(
                               'result' => true,
                               'mode' => 'none'
                          ));
        $this->setBuilder($b);
    }
}