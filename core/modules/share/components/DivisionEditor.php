<?php
/**
 * @file
 * DivisionEditor, SampleDivisionEditor
 *
 * It contains the definition to:
 * @code
final class DivisionEditor;
 * interface SampleDivisionEditor;
 * @endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\components;

use Energine\apps\gears\AdsManager;
use Energine\share\gears;
use Energine\share\gears\Builder;
use Energine\share\gears\Data;
use Energine\share\gears\DataDescription;
use Energine\share\gears\DivisionSaver;
use Energine\share\gears\Document;
use Energine\share\gears\Field;
use Energine\share\gears\FieldDescription;
use Energine\share\gears\JSONCustomBuilder;
use Energine\share\gears\JSONDivBuilder;
use Energine\share\gears\QAL;
use Energine\share\gears\SystemException;
use Energine\share\gears\TagManager;

/**
 * Division editor.
 *
 * @code
final class DivisionEditor;
 * @endcode
 *
 * @final
 */
class DivisionEditor extends Grid implements SampleDivisionEditor {
    /**
     * Site editor.
     * @var SiteEditor $siteEditor
     */
    private $siteEditor;
    /**
     * Translation editor.
     * @var TranslationEditor $transEditor
     */
    private $transEditor;
    /**
     * User editor.
     * @var UserEditor $userEditor
     */
    protected $userEditor;
    /**
     * Role editor.
     * @var RoleEditor $roleEditor
     */
    protected $roleEditor;
    /**
     * Language editor.
     * @var LanguageEditor $langEditor
     */
    protected $langEditor;
    /**
     * Widget editor.
     * @var WidgetsRepository $widgetEditor
     */
    private $widgetEditor;

    /**
     * @copydoc Grid::__construct
     */
    public function __construct($name,  array $params = null) {
        parent::__construct($name, $params);
        $this->setTableName('share_sitemap');
        $this->setTitle($this->translate('TXT_DIVISION_EDITOR'));
        $this->setParam('recordsPerPage', false);
        $sp = $this->getStateParams(true);

        if($this->getParam('site') && is_numeric($this->getParam('site')) && !isset($sp['site_id'])){
            $this->setStateParam('site_id', (int)$this->getParam('site'));
        }
    }

    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            [
                'site' => false,
             //   'smap_id' => false
            ]
        );
    }

    /**
     * Build tab of rights.
     *
     * @param int $id Division ID.
     *
     * @note By creation of new division use parent ID.
     */
    private function buildRightsTab($id) {
        $builder = new Builder($this->getTitle());

        //получаем информацию о всех группах имеющихся в системе
        $groups =
            $this->dbh->select('user_groups', ['group_id', 'group_name']);
        $groups = convertDBResult($groups, 'group_id');
        //создаем матриц
        //название группы/перечень прав
        foreach (array_keys($groups) as $groupID) {
            $res[] = ['right_id' => 0, 'group_id' => $groupID];
        }

        $resultData = new Data();
        $resultData->load($res);
        $builder->setData($resultData);

        $rightsField = $resultData->getFieldByName('right_id');
        $groupsField = $resultData->getFieldByName('group_id');


        //создаем переменную содержащую идентификторы групп в которые входит пользователь
        $data =
            $this->dbh->select('share_access_level', ['group_id', 'right_id'], ['smap_id' => $id]);

        if ($data) {
            $data = convertDBResult($data, 'group_id', true);

            for ($i = 0; $i < $resultData->getRowCount(); $i++) {

                //если установлены права для группы  - изменяем в объекте данных
                if (isset($data[$groupsField->getRowData($i)])) {
                    $rightsField->setRowData($i, $data[$groupsField->getRowData($i)]['right_id']);
                }

                $groupsField->setRowProperty($i, 'group_id', $groupsField->getRowData($i));
            }
        }


        for ($i = 0; $i < $resultData->getRowCount(); $i++) {
            $groupsField->setRowProperty($i, 'group_id', $groupsField->getRowData($i));
            $groupsField->setRowData($i, $groups[$groupsField->getRowData($i)]['group_name']);
        }

        $resultDD = new DataDescription();
        $fd = new FieldDescription('group_id');
        $fd->setSystemType(FieldDescription::FIELD_TYPE_STRING);
        $fd->setMode(FieldDescription::FIELD_MODE_READ);
        $fd->setLength(30);
        $resultDD->addFieldDescription($fd);

        $fd = new FieldDescription('right_id');
        $fd->setSystemType(FieldDescription::FIELD_TYPE_SELECT);
        $data =
            $this->dbh->select('user_group_rights', ['right_id', 'right_const as right_name']);
        $data =
            array_map(function ($a) {
                $a["right_name"] = translate("TXT_" . $a["right_name"]);
                return $a;
            }, $data);
        $data[] =
            ['right_id' => 0, 'right_name' => $this->translate('TXT_NO_RIGHTS')];

        $fd->loadAvailableValues($data, 'right_id', 'right_name');
        $resultDD->addFieldDescription($fd);

        $builder->setDataDescription($resultDD);
        $builder->build();

        $field = new Field('page_rights');
        for ($i = 0;
             $i < count(E()->getLanguage()->getLanguages()); $i++) {
            $field->addRowData(
                $builder->getResult()
            );
        }
        $this->getData()->addField($field);
    }

    /**
     * @copydoc Grid::createDataDescription
     */
    // Для setRole создаем свое описание данных
    // Для поля smap_pid формируется Дерево разделов
    protected function createDataDescription() {
        $result = parent::createDataDescription();
        //для редактирования и добавления нужно сформировать "красивое дерево разделов"
        if (in_array($this->getState(), ['add', 'edit'])) {
            $fd = $result->getFieldDescriptionByName('smap_pid');
            $fd->setType(FieldDescription::FIELD_TYPE_STRING);
            //$fd->setMode(FieldDescription::FIELD_MODE_READ);
            $result->getFieldDescriptionByName('smap_name')->removeProperty('nullable');
            if($this->getState() == 'edit'){
                $fd = new FieldDescription('smap_last_mod');
                $fd->setType(FieldDescription::FIELD_TYPE_DATETIME);
                $fd->setProperty('outputFormat', '%E');
                $fd->setProperty('tableName', $this->getTableName());
                $fd->setMode(FieldDescription::FIELD_MODE_READ);
                $result->addFieldDescription($fd, DataDescription::FIELD_POSITION_AFTER, 'smap_id');
                
            }
        } else {
            //Для режима списка нам нужно выводить не значение а ключ
            if ($this->getType() == self::COMPONENT_TYPE_LIST) {            
                $smapPIDFieldDescription =
                    $result->getFieldDescriptionByName('smap_pid');
                if ($smapPIDFieldDescription) {
                    $smapPIDFieldDescription->setType(FieldDescription::FIELD_TYPE_INT);
                }
            }
            if ($this->getState() == 'getRawData') {
                $field = new FieldDescription('smap_segment');
                $field->setType(FieldDescription::FIELD_TYPE_STRING);
                $field->setProperty('tableName', $this->getTableName());
                $result->addFieldDescription($field);
            }
        }
        return $result;
    }

    /**
     * Load template data.
     * Этот список загружается в соответствующий FieldDescription
     *
     * @param string $type Template type (layout/content).
     * @param string $siteFolder Site folder.
     * @param bool|string $oldValue Old value.
     * @return array
     */
    private function loadTemplateData($type, $siteFolder, $oldValue = false) {
        $result = [];
        $dirPath = Document::TEMPLATES_DIR . $type . '/';

        $folders = [];
        $includeFile = SITE_DIR . '/modules/' . $siteFolder . '/templates/' . $type . '.include';

        if (file_exists($includeFile)) {
            $includeRules = file($includeFile);
            foreach ($includeRules as $rule) {
                $rule = trim($rule);
                if (empty($rule)) continue;
                $folders[] = glob($dirPath . $rule);
            }
        } else {
            $folders = [
                glob($dirPath . "*." . $type . ".xml"),
                glob($dirPath . $siteFolder . "/*." . $type . ".xml"),
            ];
        }

        $r = [];
        foreach ($folders as $folder) {
            if ($folder === false) $folder = [];
            foreach ($folder as $folderPath) {
                $r[basename($folderPath)] = $folderPath;
            }
        }
        $d = new \DOMDocument('1.0', 'UTF-8');
        foreach ($r as $path) {
            $path = str_replace($dirPath, '', $path);
            list($name, $tp) = explode('.', substr(basename($path), 0, -4));
            $name = $this->translate(strtoupper($tp . '_' . $name));

            $row = [
                'key' => $path,
                'value' => $name,
            ];
            $shortPath = $path;
            if (($type == 'content') && (file_exists($path = Document::TEMPLATES_DIR . $type . '/' . $path))) {
                $d->load($path);

                if ($attr = $d->documentElement->getAttribute('segment')) {
                    $row['data-segment'] = $attr;
                }
                if ($attr = $d->documentElement->getAttribute('layout')) {
                    $row['data-layout'] = $attr;
                }
                if ($d->documentElement->hasAttribute('unique')) {
                    if($this->dbh->getScalar('share_sitemap', 'count(*)', ['smap_content' => $shortPath, 'site_id' => E()->getSiteManager()->getCurrentSite()->id])>0){
                        $row['disabled'] = 'disabled';
                        $row['unique'] = 'unique';
                    }

                }
                if($d->documentElement->hasAttribute('disabled')){
                    continue;
                }
            }
            array_push($result, $row);
        }

        unset($d);
        if ($oldValue && !in_array($dirPath . $oldValue, array_values($r))) {
            $result[] = [
                'key' => $oldValue,
                'value' => $oldValue,
                'disabled' => 'disabled'
            ];
        }

        usort($result, function ($rowA, $rowB) {
            return $rowA['value'] > $rowB['value'];
        });
        return $result;
    }

    /**
     * @copydoc Grid::loadData
     */
    // Добавляет данные об УРЛ
    protected function loadData() {
        $result = parent::loadData();

        if ($result && $this->getState() == 'getRawData') {
            $params = $this->getStateParams(true);

            $result = array_map(
                function ($val) use ($params) {
                    $val["smap_segment"] = E()->getMap($params['site_id'])->getURLByID($val["smap_id"]);
                    if ($this->getDataDescription()->getFieldDescriptionByName('site')) {
                        $val["site"] = E()->getSiteManager()->getSiteByID($params['site_id'])->base;
                    }
                    return $val;
                }, $result);
        }
        return $result;
    }

    /**
     * @copydoc Grid::getRawData
     */
    protected function getRawData() {
        $params = $this->getStateParams(true);
        $this->setFilter(['site_id' => $params['site_id']]);

        $this->setParam('onlyCurrentLang', true);
        $this->getConfig()->setCurrentState(self::DEFAULT_STATE_NAME);
        $this->setBuilder(new JSONDivBuilder());

        $this->setDataDescription($this->createDataDescription());

        $this->getBuilder()->setDocumentId($this->document->getID());
        $this->getBuilder()->setDataDescription($this->getDataDescription());

        $data = $this->createData();
        if ($data instanceof Data) {
            $this->setData($data);
            $this->getBuilder()->setData($this->getData());
        }

        $this->getBuilder()->build();

    }

    /**
     * @copydoc Grid::prepare
     */
    // Подменяем построитель для метода setPageRights
    protected function prepare() {
        parent::prepare();
        if (in_array($this->getState(), ['add', 'edit'])) {
            $this->addTranslation('ERR_NO_DIV_NAME');
            list($pageID) = $this->getStateParams();
            $this->getDataDescription()->getFieldDescriptionByName('smap_pid')->setProperty('base', E()->getSiteManager()->getSiteByPage($pageID)->base);
        }
    }

    /**
     * @copydoc Grid::save
     */
    // добавлено значение урла страницы
    protected function save() {
        $this->setSaver(
            new DivisionSaver()
        );
        $this->setBuilder(new JSONCustomBuilder());

        $transactionStarted = $this->dbh->beginTransaction();

        $result = $this->saveData();
        if (is_int($result)) {
            $mode = 'insert';
            $id = $result;
            /*Тут пришлось пойти на извращаения для получения УРЛа страницы, поскольку новосозданная страница еще не присоединена к дереву*/
            //$smapPID = simplifyDBResult($this->dbh->select('share_sitemap', 'smap_pid', array('smap_id'=>$id)), 'smap_pid', true);
            $smapPID =
                $this->getSaver()->getData()->getFieldByName('smap_pid')->getRowData(0);
            $url = $_POST[$this->getTableName()]['smap_segment'] . '/';
            if ($smapPID) {
                $url = E()->getMap(
                        E()->getSiteManager()->getSiteByPage($smapPID)->id
                    )->getURLByID($smapPID) . $url;
            }
        } else {
            $mode = 'update';
            $id = $this->getFilter();
            $id = $id['smap_id'];
            $url =
                E()->getMap(E()->getSiteManager()->getSiteByPage($id)->id)->getURLByID($id);
        }

        //Ads
        //        $ads = new AdsManager($result, $this->getState());
        //        $adsID = $ads->save();


        $transactionStarted = !($this->dbh->commit());
        if($this->logClass){
            $logger = new $this->logClass(get_class($this), $this->getName());
            $logger->write($this->getSaver()->getMode(), $this->getSaver()->getData()->asArray(true));

        }
        $b = $this->getBuilder();
        $b->setProperty('result', true)->setProperty('mode', $mode)->setProperty('url', $url);
    }

    /**
     * @copydoc Grid::add
     */
    protected function add() {
        parent::add();

        //@todo Тут пришлось пойти на извращение
        $actionParams = $this->getStateParams(true);
        $this->buildRightsTab($actionParams['pid']);

        $this->getDataDescription()->getFieldDescriptionByName('smap_segment')->removeProperty('nullable');
        $site = E()->getSiteManager()->getSiteByPage($actionParams['pid']);
        $sitemap = E()->getMap($site->id);

        $this->getData()->getFieldByName('site_id')->setData($site->id, true);

        $field = $this->getData()->getFieldByName('smap_pid');
        $smapSegment = $sitemap->getURLByID($actionParams['pid']);

        foreach ([Document::TMPL_CONTENT, Document::TMPL_LAYOUT] as $type)
            if ($f = $this->getDataDescription()->getFieldDescriptionByName(
                'smap_' . $type)
            ) {
                $f->setType(FieldDescription::FIELD_TYPE_SELECT);
                $f->loadAvailableValues(
                    $this->loadTemplateData($type, $site->folder),
                    'key', 'value');
            }


        if ($name = $this->dbh->getScalar(
            $this->getTranslationTableName(),
            ['smap_name'],
            [
                'smap_id' => $actionParams['pid'],
                'lang_id' => $this->document->getLang()])
        ) {
            for ($i = 0,
                 $langCount = count(E()->getLanguage()->getLanguages());
                 $i < $langCount; $i++) {
                $field->setRowData($i, $actionParams['pid']);
                $field->setRowProperty($i, 'data_name', $name);
                $field->setRowProperty($i, 'segment', $smapSegment);
            }
        }

        $tm = new TagManager($this->getDataDescription(), $this->getData(), $this->getTableName());
        $tm->createFieldDescription();
        $tm->createField('menu');

        //Ads
        if (class_exists('AdsManager', false)
            && AdsManager::isActive()
        ) {
            $ads = new AdsManager();
            $ads->add($this->getDataDescription());
        }
    }

    /**
     * @copydoc Grid::edit
     */
    protected function edit() {
        parent::edit();
        $this->buildRightsTab($smapID = $this->getData()->getFieldByName('smap_id')->getRowData(0));

        //Выводим УРЛ в поле сегмента
        $field = $this->getData()->getFieldByName('smap_pid');
        $site =
            E()->getSiteManager()->getSiteByID($this->getData()->getFieldByName('site_id')->getRowData(0));

        foreach ([Document::TMPL_CONTENT, Document::TMPL_LAYOUT] as $type)
            if ($f = $this->getDataDescription()->getFieldDescriptionByName(
                'smap_' . $type)
            ) {
                $f->setType(FieldDescription::FIELD_TYPE_SELECT);
                $old_value = $this->getData()->getFieldByName('smap_' . $type)->getRowData(0);
                $template_data = $this->loadTemplateData($type, $site->folder, $old_value);
                $f->loadAvailableValues(
                    $template_data,
                    'key', 'value');
            }

        //Если изменен  - вносим исправления в список возможных значений
        if ($contentXMLFieldData = $this->dbh->getScalar($this->getTableName(), 'smap_content_xml', ['smap_id' => $this->getData()->getFieldByName('smap_id')->getRowData(0)])) {
            $contentFilename =
                $this->getData()->getFieldByName('smap_content')->getRowData(0);
            $contentFD =
                $this->getDataDescription()->getFieldDescriptionByName('smap_content');
            $contentFD->setProperty('reset', $this->translate('TXT_RESET_CONTENT'));
            $av = &$contentFD->getAvailableValues();
            if (isset($av[$contentFilename])) {
                $av[$contentFilename]['value'] .=
                    ' - ' . $this->translate('TXT_CHANGED');
            }
            $newField = new FieldDescription('smap_content_xml');
            $newField->setProperty('nullable', true);
            $newField->setType(FieldDescription::FIELD_TYPE_CODE);
            $newField->setProperty('tableName', $this->getTableName());
            $newField->setProperty('tabName', $contentFD->getPropertyValue('tabName'));

            $this->getDataDescription()->addFieldDescription($newField, DataDescription::FIELD_POSITION_AFTER, 'smap_content');
            $newField = new Field('smap_content_xml');
            $doc = new \DOMDocument();
            $doc->loadXML($contentXMLFieldData);
            $doc->formatOutput = true;
            $doc->preserveWhiteSpace = true;
            $newField->setData($doc->saveXML(null, LIBXML_NOEMPTYTAG), true);
            $this->getData()->addField($newField);
            unset($contentFilename, $contentFD, $av, $doc, $newField);

        }
        $smapSegment = '';
        if ($field->getRowData(0) !== null) {
            $smapSegment =
                E()->getMap($site->id)->getURLByID($field->getRowData(0));

            $this->getDataDescription()->getFieldDescriptionByName('smap_segment')->removeProperty('nullable');
        } else {
            $this->getDataDescription()->getFieldDescriptionByName('smap_pid')
                ->setMode(FieldDescription::FIELD_MODE_READ)
                ->setType(FieldDescription::FIELD_TYPE_HIDDEN);
            foreach (
                [
                    'smap_is_disabled',
                    'smap_segment',
                    //'smap_pid',
                    'smap_redirect_url',
                    'smap_meta_robots',
                    'smap_meta_keywords',
                    'smap_meta_description'
                ]
                as
                $fieldName
            ) {
                if($f = $this->getDataDescription()->getFieldDescriptionByName($fieldName))
                $this->getDataDescription()->removeFieldDescription($f);
            }
        }
        $smapName = $this->dbh->getScalar($this->getTranslationTableName(), 'smap_name', ['smap_id' => $field->getRowData(0), 'lang_id' => $this->document->getLang()]);

        for ($i = 0; $i < (
        $langs = count(E()->getLanguage()->getLanguages())); $i++) {
            $field->setRowProperty($i, 'data_name', $smapName);
            $field->setRowProperty($i, 'segment', $smapSegment);
        }

        $tm = new TagManager(
            $this->getDataDescription(),
            $this->getData(),
            $this->getTableName()
        );
        $tm->createFieldDescription();
        $tm->createField();

        $this->getDataDescription()->getFieldDescriptionByName('smap_id')->setType(FieldDescription::FIELD_TYPE_INT)->setMode(FieldDescription::FIELD_MODE_READ);

        if (class_exists('AdsManager', false)
            && AdsManager::isActive()
        ) {
            $ads = new AdsManager();
            $ads->edit($this->getData(), $this->getDataDescription());
        }
    }

    /**
     * @copydoc Grid::main
     */
    // Добавлен перевод для корня дерева разделов
    protected function main() {
        parent::main();
        $params = $this->getStateParams(true);

        if ($params) {
            $siteID = $params['site_id'];
        } else {
            $siteID = E()->getSiteManager()->getCurrentSite()->id;
        }

        $this->setProperty('site', $siteID);
        $this->setFilter(['site_id' => $siteID]);
        $this->addTranslation('TXT_DIVISIONS');
    }


    /**
     * @copydoc Grid::deleteData
     *
     * @throws SystemException 'ERR_DEV_BAD_DATA'
     */
    // Не позволяет удалить раздел по умолчанию а также системные разделы
    protected function deleteData($id) {
        if (!($PID = $this->dbh->getScalar('share_sitemap', ['smap_pid'], [$this->getPK() => $id])))
            throw new SystemException('ERR_DEV_BAD_DATA', SystemException::ERR_CRITICAL);
        if (empty($PID)) {
            $PID = null;
        }
        $this->setFilter(['smap_pid' => $PID]);
        parent::deleteData($id);
    }

    /**
     * Show widget editor.
     */
    protected function showWidgetEditor() {
        $this->request->shiftPath(1);
        $this->widgetEditor =
            $this->document->componentManager->createComponent('widgetEditor', 'Energine\share\components\WidgetsRepository', ['config' => 'ModalWidgetsRepository.component.xml']);
        $this->widgetEditor->run();
    }


    // Для метода show слешатся имена разделов
    public function build() {
        switch ($this->getState()) {
            case 'showPageToolbar':
                $result = false;
                // вызываем родительский метод построения
                $result = Component::build();
                if ($result instanceof \DOMDocument) {
                    $result->documentElement->appendChild($result->importNode($this->buildJS(), true));
                    $tbs = $this->getToolbar();
                    if (!empty($tbs))
                        foreach ($tbs as $toolbar) {
                            $result->documentElement->appendChild($result->importNode($toolbar->build(), true));
                        }
                }
                break;
            case 'showTransEditor':
                $result = $this->transEditor->build();
                break;
            case 'showUserEditor':
                $result = $this->userEditor->build();
                break;
            case 'showRoleEditor':
                $result = $this->roleEditor->build();
                break;
            case 'showLangEditor':
                $result = $this->langEditor->build();
                break;
            case 'showSiteEditor':
                $result = $this->siteEditor->build();
                break;
            case 'showWidgetEditor':
                $result = $this->widgetEditor->build();
                break;
            default:
                $result = parent::build();
                break;
        }

        return $result;
    }

    /**
     * Get node property.
     *
     * @throws SystemException 'ERR_404'
     */
    protected function getProperties() {

        $id = $_POST['id'];
        $langID = $_POST['languageID'];
        if (!$this->recordExists($id)) {
            throw new SystemException('ERR_404', SystemException::ERR_404);
        }

        $this->setFilter(['smap_id' => $id, 'lang_id' => $langID]);
        $result = $this->dbh->select(
            'SELECT smap_name, smap_pid, smap_order_num ' .
            ' FROM share_sitemap s' .
            ' LEFT JOIN share_sitemap_translation st ON s.smap_id = st.smap_id' .
            ' WHERE s.smap_id = ' . $id . ' AND lang_id = ' . $langID
        );
        list($result) = $result;
        $b = new JSONCustomBuilder();
        $b->setProperty('result', true);
        $b->setProperty('data', $result);
        $this->setBuilder($b);
    }

    /**
     * Get template information.
     */
    protected function getTemplateInfo() {
        $res = $this->dbh->select('SELECT smap_layout, smap_content, IF(smap_content_xml<>"", 1,0 ) as modified FROM share_sitemap WHERE smap_id = %s', $this->document->getID());
        if ($res) {
            list($res) = $res;

            list($contentTitle) = explode('.', basename($res['smap_content']));
            list($layoutTitle) = explode('.', basename($res['smap_layout']));

            $result = [
                'content' => [
                    'title' => $this->translate('TXT_CONTENT'),
                    'file' => $res['smap_content'],
                    'name' => $this->translate('CONTENT_' . $contentTitle),
                    'modified' => ((bool)$res['modified']) ? $this->translate('TXT_CHANGED') : false
                ],
                'layout' => [
                    'title' => $this->translate('TXT_LAYOUT'),
                    'file' => $res['smap_layout'],
                    'name' => $this->translate('LAYOUT_' . $layoutTitle),
                ],
                'actionSelector' => [
                    'reset' => $this->translate('TXT_RESET_CONTENT'),
                    'save' => $this->translate('TXT_SAVE_CONTENT'),
                    'saveTemplate' => $this->translate('TXT_SAVE_TO_CURRENT_CONTENT'),
                    'saveNewTemplate' => $this->translate('TXT_SAVE_TO_NEW_CONTENT')
                ],
                'actionSelectorText' => $this->translate('TXT_ACTION_SELECTOR'),
                'saveText' => $this->translate('BTN_APPLY'),
                'cancelText' => $this->translate('BTN_CANCEL')
            ];
            //С точкой это хитрый POSIX стандарт
            //То есть если страница создана не по шаблону из ядра
            //и существует одноименный шаблон ядра
            //то добавляется опция возможности откатиться к шаблону ядра
            if ((dirname($res['smap_content']) != '.')
                && file_exists('templates/content/' . basename($res['smap_content']))
            ) {
                $result['actionSelector']['revert'] = $this->translate('TXT_REVERT_CONTENT');
            }
        }
        $b = new JSONCustomBuilder();
        $b->setProperty('result', true);
        $b->setProperty('data', $result);
        $this->setBuilder($b);
    }

    /**
     * Show page toolbar.
     */
    protected function showPageToolbar() {
        if (!$this->getConfig()->getCurrentStateConfig()) {
            throw new SystemException('ERR_DEV_TOOLBAR_MUST_HAVE_CONFIG', SystemException::ERR_DEVELOPER);
        }
        $this->addToolbar($this->loadToolbar());

        if ($this->document->isEditable())
            $this->getToolbar('main_toolbar')->getControlByID('editMode')->setState(1);

        foreach($this->getToolbar('main_toolbar')->getControls() as $control){
            if($control->getAttribute('onclick') == 'jumpSite'){
                foreach (E()->getSiteManager() as $site) {
                    $params = [];
                    if($site == E()->getSiteManager()->getCurrentSite()){
                        $params['selected'] = 'selected';
                    }
                    $control->addItem($site->base, $site->name, $params);
                }
                break;
            }
        }
    }

    /**
     * Selector.
     */
    protected function selector() {
        $this->addTranslation('TXT_DIVISIONS');
        $this->prepare();

        $params = $this->getStateParams(true);

        if ($params) {
            $siteID = $params['site_id'];
        } else {
            $siteID = E()->getSiteManager()->getCurrentSite()->id;
        }

        $this->setProperty('site', $siteID);
        $this->setFilter(['site_id' => $siteID]);
    }


    /**
     * Show translation editor.
     */
    protected function showTransEditor() {
        $this->request->shiftPath(1);
        $this->transEditor =
            $this->document->componentManager->createComponent('transEditor', 'Energine\share\components\TranslationEditor', null);
        $this->transEditor->run();
    }

    /**
     * Show user editor.
     */
    protected function showUserEditor() {
        $this->request->shiftPath(1);
        $this->userEditor =
            $this->document->componentManager->createComponent('userEditor', 'Energine\user\components\UserEditor', null);
        $this->userEditor->run();
    }

    /**
     * Show role editor.
     */
    protected function showRoleEditor() {
        $this->request->shiftPath(1);
        $this->roleEditor =
            $this->document->componentManager->createComponent('roleEditor', 'Energine\user\components\RoleEditor', null);
        $this->roleEditor->run();
    }

    /**
     * Show language editor.
     */
    protected function showLangEditor() {
        $this->request->shiftPath(1);
        $this->langEditor =
            $this->document->componentManager->createComponent('langEditor', 'Energine\share\components\LanguageEditor', null);
        $this->langEditor->run();
    }

    protected function fileLibrary() {
        $this->request->shiftPath(1);

        $this->fileLibrary = $this->document->componentManager->createComponent('filelibrary', 'Energine\share\components\FileRepository', ['config' => 'core/modules/share/config/FileRepositoryModal.component.xml']);

        $this->fileLibrary->run();
    }

    /**
     * Show site editor.
     */
    protected function showSiteEditor() {
        $this->request->shiftPath(1);
        $this->siteEditor =
            $this->document->componentManager->createComponent('siteEditor', 'Energine\share\components\SiteEditor', ['config' => 'core/modules/share/config/SiteEditorModal.component.xml']);
        $this->siteEditor->run();
    }

    /**
     * Reset content template.
     * @note XML content code taken from the file.
     */
    protected function resetTemplates() {
        $ap = $this->getStateParams(true);
        $filter = ['smap_id' => $this->document->getID()];
        if (isset($ap['site_id'])) {
            $filter = ['site_id' => $ap['site_id']];
        } elseif (isset($ap['smap_id'])) {
            $filter = ['smap_id' => $ap['smap_id']];
        }

        $smapID = $this->dbh->getColumn($this->getTableName(), ['smap_id'], $filter);
        $this->dbh->beginTransaction();
        if (is_array($smapID) && !empty($smapID)) {
            $this->dbh->modify(
                QAL::UPDATE,
                $this->getTableName(),
                [
                    'smap_content_xml' => '',
                    'smap_layout_xml' => ''
                ],
                ['smap_id' => $smapID]
            );
        }
        $b = new JSONCustomBuilder();
        $b->setProperty('result', true);
        $this->setBuilder($b);

        $this->dbh->commit();
    }


    /**
     * @copydoc Grid::changeOrder
     */
    protected function changeOrder($direction) {

        $id = $this->getStateParams();
        list($id) = $id;
        if (!$this->recordExists($id)) {
            throw new SystemException('ERR_404', SystemException::ERR_404);
        }
        $order = $this->getOrder();
        if ($direction == Grid::DIR_UP) {
            $order[key($order)] =
                ($order[key($order)] == QAL::ASC) ? QAL::DESC : QAL::ASC;
        }
        $PID = $this->dbh->getScalar($this->getTableName(), ['smap_pid'], ['smap_id' => $id]);

        if (!is_null($PID)) {
            $PID = ' = ' . $PID;
        } else {
            $PID = 'IS NULL';
        }

        $orderFieldName = key($order);
        $request = sprintf('SELECT %s, %s
                FROM %s
                WHERE %s %s= (
                SELECT %s
                FROM %s
                WHERE %s = %s )
                AND smap_pid %s
                %s
                LIMIT 2 ',
            $this->getPK(), $orderFieldName,
            $this->getTableName(),
            $orderFieldName, $direction,
            $orderFieldName,
            $this->getTableName(),
            $this->getPK(), $id,
            $PID,
            $this->dbh->buildOrderCondition($order));

        $result = $this->dbh->select($request);
        if (!$result || sizeof($result) < 2) {
            throw new SystemException('ERR_CANT_MOVE', SystemException::ERR_NOTICE);
        }

        $result = convertDBResult($result, $this->getPK(), true);

        /**
         * @todo Тут нужно что то пооптимальней придумать для того чтобы осуществить операцию переноса значений между двумя элементами массива
         *  $a = $b;
         *  $b =$a;
         */
        $keys = array_keys($result);
        $data = [];

        $c = $result[current($keys)];
        $data[current($keys)] = $result[next($keys)];
        $data[current($keys)] = $c;

        foreach ($data as $id2 => $value) {
            $order = $value['smap_order_num'];
            $this->dbh->modify(QAL::UPDATE, $this->getTableName(), [$orderFieldName => $order], [$this->getPK() => $id2]);
            if ($id2 != $id) {
                $result = $id2;
            }
        }
        $b = new JSONCustomBuilder();
        $b->setProperties([
            'result' => true,
            'dir' => $direction,
            'nodeID' => $result
        ]);
        $this->setBuilder($b);

    }
}

/**
 * Fake interface to create sample.
 *
 * @code
interface SampleDivisionEditor;
 * @endcode
 */
interface SampleDivisionEditor {
}