<?php
/**
 * Содержит класс DomainEditor
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2011
 */

/**
 * Редактор доменов
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class DomainEditor extends Grid {
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
        $this->setTableName('share_domains');
        $filter = ' (domain_id NOT IN (SELECT domain_id FROM share_domain2site)) ';
        if ($this->getParam('siteID')) {
            $filter .= ' OR (domain_id IN (SELECT domain_id FROM share_domain2site WHERE site_id = ' . $this->getParam('siteID') . '))';
        }
        $this->setFilter($filter);
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
            $fd = $this->getDataDescription()->getFieldDescriptionByName('domain_protocol');
            $fd->setType(FieldDescription::FIELD_TYPE_SELECT);
            $fd->loadAvailableValues(array(array('key' => 'http', 'value' => 'http://'), array('key' => 'https', 'value' => 'https://')), 'key', 'value');


            if ($this->getState() == 'add') {
                $this->getData()->getFieldByName('domain_port')->setData(80, true);
                $this->getData()->getFieldByName('domain_root')->setData('/', true);
            }
            //Если редактирование и дефолтный домен
            elseif($this->getData()->getFieldByName('domain_is_default')->getRowData(0)) {
                $this->getDataDescription()->getFieldDescriptionByName('domain_is_default')->setMode(FieldDescription::FIELD_MODE_READ);
            }
        }
    }
    /**
     * При сохранении данных
     * сбрасываем флаг дефолтности
     * @return mixed
     */
    protected function loadData(){
        $result = parent::loadData();
        if ($this->getState() == 'save' && isset($result[0]['domain_is_default']) &&
            $result[0]['domain_is_default'] !== '0') {
            $this->dbh->modify(QAL::UPDATE, $this->getTableName(), array('domain_is_default' => null));
        }

        return $result;
    }
    /**
     * Добавлеям параметр идентификатор сайта
     *
     * @return array
     */
    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            array(
                 'siteID' => false,
            )
        );
    }
    /**
     * Нет смысла создавать отдельный сейвер
     * Проверяем на правильность заполнянеия поля корня сайта
     *
     * @return mixed
     */
    protected function saveData(){

        if(isset($_POST[$this->getTableName()]['domain_root']) && (substr($_POST[$this->getTableName()]['domain_root'], -1) != '/')){
            $_POST[$this->getTableName()]['domain_root'] .= '/';
        }
        return parent::saveData();
    }
}