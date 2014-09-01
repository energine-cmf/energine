<?php
/**
 * @file
 * SiteEditor
 *
 * It contains the definition to:
 * @code
class SiteEditor;
@endcode
 *
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 *
 * @version 1.0.0
 */
namespace Energine\share\components;
use Energine\share\gears\SiteSaver, Energine\share\gears\SiteEditorConfig, Energine\share\gears\FieldDescription, Energine\share\gears\Field, Energine\share\gears\TagManager, Energine\share\gears\SystemException;
/**
 * Site editor.
 *
 * @code
class SiteEditor;
@endcode
 */
class SiteEditor extends Grid {
    /**
     * Division editor.
     * @var DivisionEditor $divEditor
     */
    private $divEditor;
    /**
     * Domain editor.
     * @var DomainEditor $domainEditor
     */
    private $domainEditor;
    /**
     * Editor for site additional properties
     * @var SitePropertiesEditor
     */
    private $propertiesEditor;

    /**
     * @copydoc Grid::__construct
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setTableName('share_sites');
        $this->setSaver(new SiteSaver());
    }

    /**
     * @copydoc Grid::getConfig
     */
    protected function getConfig() {
        if (!$this->config) {
            $this->config = new SiteEditorConfig(
                $this->getParam('config'),
                get_class($this),
                $this->module
            );
        }
        return $this->config;
    }

    /**
     * @copydoc Grid::prepare
     */
    // Изменяем типы филдов
    protected function prepare() {
        parent::prepare();
        if (in_array($this->getState(), array('add', 'edit'))) {
            $fd = new FieldDescription('domains');
            $fd->setType(FieldDescription::FIELD_TYPE_TAB);
            $fd->setProperty('title', $this->translate('TAB_DOMAINS'));
            $this->getDataDescription()->addFieldDescription($fd);

            $field = new Field('domains');
            $state = $this->getState();
            $tab_url = (($state != 'add') ? $this->getData()->getFieldByName($this->getPK())->getRowData(0) : '') . '/domains/';
            $field->setData($tab_url, true);
            $this->getData()->addField($field);

            $fd = $this->getDataDescription()->getFieldDescriptionByName('site_folder');
            $fd->setType(FieldDescription::FIELD_TYPE_SELECT);
            $fd->loadAvailableValues($this->loadFoldersData(), 'key', 'value');

            if ($this->getData()->getFieldByName('site_is_default')->getRowData(0) == 1) {
                $this->getDataDescription()->getFieldDescriptionByName('site_is_default')->setMode(FieldDescription::FIELD_MODE_READ);
            }
            $tagField = new FieldDescription('tags');
            $tagField->setType(FieldDescription::FIELD_TYPE_STRING);
            $tagField->removeProperty('pattern');
            $this->getDataDescription()->addFieldDescription($tagField);

            if ($this->getState() == 'add') {
                $this->getData()->getFieldByName('site_is_active')->setData(1, true);
                $this->getData()->getFieldByName('site_is_indexed')->setData(1, true);

                //Добавляем селект позволяющий скопировать структуру одного из существующих сайтов в новый
                $fd = new FieldDescription('copy_site_structure');
                $fd->setType(FieldDescription::FIELD_TYPE_SELECT);
                $fd->loadAvailableValues($this->dbh->selectRequest('SELECT ss.site_id, site_name FROM share_sites ss LEFT JOIN share_sites_translation sst ON ss.site_id = sst.site_id WHERE lang_id =%s ', $this->document->getLang()), 'site_id', 'site_name');
                $this->getDataDescription()->addFieldDescription($fd);
            }
            else {
                $this->getDataDescription()->getFieldDescriptionByName($this->getPK())->setType(FieldDescription::FIELD_TYPE_INT)->setMode(FieldDescription::FIELD_MODE_READ);
                $tm = new TagManager($this->getDataDescription(), $this->getData(), $this->getTableName());
                $tm->createFieldDescription();
                $tm->createField();
            }
        }
    }

    /**
     * Reset editor.
     */
    protected function reset() {
        $this->request->shiftPath(1);
        $this->divEditor = $this->document->componentManager->createComponent('dEditor', 'share', 'DivisionEditor');
        $this->divEditor->run();
    }

    /**
     * Create IFRAME for tab with the list of domains.
     */
    protected function domains() {
        $sp = $this->getStateParams(true);
        $domainEditorParams = array();

        if (isset($sp['site_id'])) {
            $this->request->shiftPath(2);
            $domainEditorParams = array('siteID' => $sp['site_id']);
        }
        else {
            $this->request->shiftPath(1);
        }
        $this->domainEditor = $this->document->componentManager->createComponent('domainEditor', 'share', 'DomainEditor', $domainEditorParams);
        $this->domainEditor->run();
    }

    protected function properties() {
        $sp = $this->getStateParams(true);
        $sitePropertiesEditorParams = array();

        if (isset($sp['site_id'])) {
            $this->request->shiftPath(2);
            $sitePropertiesEditorParams = array('siteID' => $sp['site_id']);
        }

        $this->propertiesEditor = $this->document->componentManager->createComponent('propertiesEditor', 'share', 'SitePropertiesEditor', $sitePropertiesEditorParams);
        $this->propertiesEditor->run();
    }

    /**
     * @copydoc Grid::build
     */
    public function build() {
        if ($this->getState() == 'reset') {
            $result = $this->divEditor->build();
        }
        elseif ($this->getState() == 'domains') {
            $result = $this->domainEditor->build();
        }
        elseif ($this->getState() == 'properties') {
            $result = $this->propertiesEditor->build();
        }
        else {
            $result = parent::build();
        }

        return $result;
    }

    /**
     * Load data about folders into 'folder' field.
     *
     * @return array
     */
    private function loadFoldersData() {
        $result = array();
        foreach (glob(SITE_DIR . '/modules/*', GLOB_ONLYDIR) as $folder) {
            $folder = str_replace(SITE_DIR . '/modules/', '', $folder);
            $result[] = array('key' => $folder, 'value' => $folder);
        }
        return $result;
    }

    /**
     * Go.
     *
     * @throws SystemException 'ERR_BAD_URL'
     */
    protected function go() {
        list($siteID) = $this->getStateParams();
        if(!($url = simplifyDBResult(
            $this->dbh->select(
                'SELECT CONCAT( domain_protocol, "://", domain_host, ":", domain_port, domain_root ) AS url
                FROM `share_domains`
                LEFT JOIN share_domain2site
                USING ( domain_id )
                WHERE (site_id = %s)
                LIMIT 1', $siteID), 'url', true))){
            throw new SystemException('ERR_BAD_URL', SystemException::ERR_CRITICAL, $this->dbh->getLastRequest());
        }
        E()->getResponse()->setRedirect($url);
    }
}