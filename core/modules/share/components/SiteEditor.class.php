<?php
/**
 * Содержит класс SiteEditor
 *
 * @package energine
 * @subpackage share
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

/**
 * Редактор сайтов
 *
 * @package energine
 * @subpackage share
 * @author d.pavka@gmail.com
 */
class SiteEditor extends Grid {
    /**
     * @var DivisionEditor
     */
    private $divEditor;
    /**
     * @var DomainEditor
     */
    private $domainEditor;

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
        $this->setTableName('share_sites');
        $this->setSaver(new SiteSaver());
    }

    /**
     * @return GridConfig
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
     * Изменяем типы филдов
     *
     * @return DataDescription
     * @access protected
     */
    protected function prepare() {
        parent::prepare();
        if (in_array($this->getState(), array('add', 'edit'))) {
            $this->addTranslation('TAB_DOMAINS');
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
            // Добавлена проверка на наличие филда с лого сайта
            // для обеспечения обратной совсместимости.
            if($fieldLogo = $this->getDataDescription()->getFieldDescriptionByName('site_logo')) {
                $fieldLogo->setType(FieldDescription::FIELD_TYPE_FILE);
            }

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

    protected function reset() {
        $this->request->setPathOffset($this->request->getPathOffset() + 1);
        $this->divEditor = $this->document->componentManager->createComponent('dEditor', 'share', 'DivisionEditor');
        $this->divEditor->run();
    }

    /**
     * Формирование IFRAME для вклдаки с перечнем доменов
     * @return void
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

    public function build() {
        if ($this->getState() == 'reset') {
            $result = $this->divEditor->build();
        }
        elseif ($this->getState() == 'domains') {
            $result = $this->domainEditor->build();
        }
        else {
            $result = parent::build();
        }

        return $result;
    }

    /**
     * Загружаем данные о папках в поле folder
     *
     * @return array
     * @access private
     */
    private function loadFoldersData() {
        $result = array();
        foreach (glob(SITE_DIR . '/modules/*', GLOB_ONLYDIR) as $folder) {
            $folder = str_replace(SITE_DIR . '/modules/', '', $folder);
            $result[] = array('key' => $folder, 'value' => $folder);
        }
        return $result;
    }

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