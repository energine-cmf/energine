<?php
/**
 * Содержит класс SiteSaver
 *
 * @package energine
 * @subpackage share
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

/**
 * Сохранятор для редактора сайтов
 *
 * @package energine
 * @subpackage share
 * @author d.pavka@gmail.com
 */
class SiteSaver extends Saver {
    const MODULES = 'modules';
    /**
     * Конструктор класса
     *
     * @access public
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * После сохранения данных сайта, создает новый раздел , переводы и права
     *
     * @return mixed
     * @access public
     */
    public function save() {
        $mainTableName = 'share_sites';


        if (isset($_POST[$mainTableName]['site_is_default']) && $_POST[$mainTableName]['site_is_default'] !== '0') {
            $this->dbh->modify(QAL::UPDATE, $mainTableName, array('site_is_default' => 0));
        }
        $result = parent::save();
        $id = ($this->getMode() == QAL::INSERT) ? $result : $this->getData()->getFieldByName('site_id')->getRowData(0);
        //Сохраняем информацию о доменах
        $domainIDs = simplifyDBResult($this->dbh->select('SELECT domain_id FROM share_domains WHERE domain_id NOT IN (SELECT domain_id FROM share_domain2site)'), 'domain_id');

        if (!empty($domainIDs)) {
            foreach ($domainIDs as $domainID) {
                $this->dbh->modify(QAL::INSERT, 'share_domain2site', array('site_id' => $id, 'domain_id' => $domainID));
            }
        }

        //Записываем информацию в таблицу тегов
        $tm = new TagManager($this->dataDescription, $this->dataDescription, 'share_sites');
        $tm->save($id);


        if ($this->getMode() == QAL::INSERT) {
            //При создании нового проекта   ищем параметр конфигурации указывающий на идентификатор 
            //шаблонного раздела
            if (isset($_POST['copy_site_structure'])) {
                $this->copyStructure((int)$_POST['copy_site_structure'], $id);
            } else {
                $this->createMainPage($id);
            }
        }
        return $result;
    }

    /**
     * Создаем главную страницу сайта
     * И назначаем на нее права
     * @param $id
     */
    private function createMainPage($id) {
        //Ищем в перечне шаблонов модуля по которому создан сайт, шаблоны отмеченные аттрибутом default
        //если не находим - берем default.[type].xml
        //а если и такого нет - берем шаблон default.[type].xml из ядра ..а что делать?
        $module = $this->getData()->getFieldByName('site_folder')->getRowData(0);

        $content = $layout = false;
        foreach(array('content', 'layout') as $type){
            foreach(glob(implode(DIRECTORY_SEPARATOR, array(SITE_DIR, self::MODULES, $module, 'templates', $type, '*'))) as $path){
                if($xml = simplexml_load_file($path)){
                    $attrs = $xml->attributes();
                    if(isset($attrs['default'])){
                        $$type = $module.'/'.basename($path);
                        break;
                    }
                }
            }

            if(!$$type && file_exists(implode(DIRECTORY_SEPARATOR, array(SITE_DIR, self::MODULES, $module, 'templates', $type, 'default.'.$type.'.xml')))){
                $$type = $module.'/'.'default.'.$type.'.xml';
            }
            if(!$$type){
                $$type = 'default.'.$type.'.xml';
            }

            //Если шаблона там не окажется
            //та пошло оно в жопу ...это ж не для ядерного реактора ПО
        }


        $translationTableName = 'share_sites_translation';
        //Если не задан параметр конфигурации  - создаем одну страницу
        $smapId = $this->dbh->modify(QAL::INSERT, 'share_sitemap', array('smap_content' => $content, 'smap_layout' => $layout, 'site_id' => $id, 'smap_segment' => QAL::EMPTY_STRING));
        foreach ($_POST[$translationTableName] as $langID => $siteInfo) {
            $this->dbh->modify(
                QAL::INSERT,
                'share_sitemap_translation',
                array(
                    'lang_id' => $langID,
                    'smap_id' => $smapId,
                    'smap_name' => $siteInfo['site_name']
                )
            );
        }
        //права берем ориентируясь на главную страницу дефолтного сайта
        $this->dbh->modifyRequest(
            'INSERT IGNORE INTO share_access_level ' .
            '(smap_id, right_id, group_id) ' .
            'SELECT %s, al.right_id, al.group_id ' .
            'FROM `share_access_level` al ' .
            'LEFT JOIN share_sitemap s ON s.smap_id = al.smap_id ' .
            'WHERE s.smap_pid is NULL AND site_id= %s',
            $smapId,
            E()->getSiteManager()->getDefaultSite()->id
        );
    }

    /**
     * Копирование структуры выбранного сайта в новый
     *
     * @param $sourceSiteID
     * @param $destinationSiteID
     */
    private function copyStructure($sourceSiteID, $destinationSiteID) {
        $source = $this->dbh->select(
            'share_sitemap',
            array('smap_id', 'smap_layout', 'smap_content', 'smap_pid', 'smap_segment', 'smap_order_num', 'smap_redirect_url'),
            array('site_id' => $sourceSiteID)
        );

        if (is_array($source)) {
            $oldtoNewMAP = $this->copyRows($source, null, '', $destinationSiteID);
            foreach ($oldtoNewMAP as $oldID => $newID) {
                $this->dbh->modifyRequest('
                INSERT INTO share_sitemap_translation( 
                    smap_id, 
                    lang_id, 
                    smap_name, 
                    smap_description_rtf, 
                    smap_html_title, 
                    smap_meta_keywords, 
                    smap_meta_description, 
                    smap_is_disabled) 
                SELECT 
                    %s, 
                    lang_id, 
                    smap_name, 
                    smap_description_rtf, 
                    smap_html_title, 
                    smap_meta_keywords, 
                    smap_meta_description, 
                    smap_is_disabled
                 FROM share_sitemap_translation
                 WHERE smap_id = %s
                 ', $newID, $oldID
                );
                $this->dbh->modifyRequest(
                    'INSERT INTO share_sitemap_tags(smap_id, tag_id)
                     SELECT %s, tag_id
                        FROM share_sitemap_tags
                        WHERE smap_id = %s
                    ', $newID, $oldID
                );
                $this->dbh->modifyRequest(
                    'INSERT INTO share_access_level ' .
                    '(smap_id, right_id, group_id) ' .
                    'SELECT %s, al.right_id, al.group_id ' .
                    'FROM `share_access_level` al ' .
                    'WHERE al.smap_id = %s', $newID, $oldID
                );
            }
        }
        //throw new SystemException('sdsdsd');
    }

    /**
     * Рекурсивный итератор по набору данных для копирования
     *
     * @param $source
     * @param $PID
     * @param $newPID
     * @param $siteID
     * @return array
     */
    private function copyRows($source, $PID, $newPID, $siteID) {
        $result = array();
        //inspect(func_get_args());
        foreach ($source as $key => $row) {
            if ($row['smap_pid'] == $PID) {

                $newRow = $row;
                $newRow['site_id'] = $siteID;
                $newRow['smap_pid'] = $newPID;
                if ($row['smap_segment'] === '') $newRow['smap_segment'] = QAL::EMPTY_STRING;
                $oldPID = $row['smap_id'];
                unset($newRow['smap_id']);
                //unset($source[$key]);
                $result += $this->copyRows($source, $oldPID, $result[$row['smap_id']] = $this->dbh->modify(QAL::INSERT, 'share_sitemap', $newRow), $siteID);
            }
        }

        return $result;
    }
}