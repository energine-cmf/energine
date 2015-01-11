<?php
/**
 * @file
 * WidgetsRepository
 *
 * It contains the definition to:
 * @code
abstract class DataSet;
@endcode
 *
 * @author spacelord
 * @copyright Energine 2010
 *
 * @version 1.0.0
 */
namespace Energine\share\components;
use Energine\share\gears\QAL, Energine\share\gears\FieldDescription, Energine\share\gears\SystemException, Energine\share\gears\ComponentManager, Energine\share\gears\Field, Energine\share\gears\Data, Energine\share\gears\DataDescription, Energine\share\gears\Builder, Energine\share\gears\JSONCustomBuilder, Energine\share\gears\Translit;
/**
 * Class to work with repository widgets.
 *
 * @code
abstract class DataSet;
@endcode
 */
class WidgetsRepository extends Grid {
    /**
     * Temporary component.
     * @var Component $tmpComponent
     */
    private $tmpComponent;

    /**
     * @copydoc Grid::__construct
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setTableName('share_widgets');
        $this->setOrder(array('widget_name' => QAL::ASC));
    }

    /**
     * @copydoc Grid::createDataDescription
     */
    protected function createDataDescription(){
        $result = parent::createDataDescription();
        if(in_array($this->getState(), array('add', 'edit'))){
            $result->getFieldDescriptionByName('widget_xml')->setType(FieldDescription::FIELD_TYPE_CODE);
        }
        return $result;
    }

    /**
     * Build for for editing component parameters.
     * The form creation is based on XML widget's data received per POST request.
     *
     * @throws SystemException 'ERR_INSUFFICIENT_DATA'
     * @throws SystemException 'ERR_BAD_XML_DESCR'
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
     * Build widget.
     *
     * @throws SystemException 'ERR_BAD_DATA'
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
     * @copydoc Grid::loadData
     */
    // Для формы ввода имени для нового шаблона контента отключаем получение данных; а какие там могут быть данные  - в самом то деле
    protected function loadData() {
        if ($this->getState() == 'showNewTemplateForm') return false;

        return parent::loadData();
    }

    /**
     * @copydoc Grid::build
     */
    // Для состояния buildWidget возвращаем код динамического компонента
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
     * Save content for current page.
     *
     * @throws SystemException 'ERR_INSUFFICIENT_DATA'
     * @throws SystemException 'ERR_BAD_XML'
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
     * Show form for new template.
     */
    protected function showNewTemplateForm() {
        $this->setType(self::COMPONENT_TYPE_FORM_ADD);
        $this->prepare();
    }

    /**
     * Save template.
     * It saves the position of all blocks in the current template.
     * If this is one of the core template then it creates new template with the same name
     * and reset him for current page and for all pages, created from this template.
     *
     * @throws SystemException 'ERR_INSUFFICIENT_DATA'
     * @throws SystemException 'ERR_BAD_XML'
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
     * Create symlink.
     * It return the path to the symlink.
     *
     * @param string $fileName Filename.
     * @param string $module Module name.
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
     * Save new template.
     *
     * @throws SystemException 'ERR_INSUFFICIENT_DATA'
     * @throws SystemException 'ERR_BAD_XML'
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
     * Revert this template to the initial template.
     *
     * @throws SystemException 'ERR_CONTENT_NOT_REVERTED'
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