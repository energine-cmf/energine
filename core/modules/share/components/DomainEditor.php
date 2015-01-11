<?php
/**
 * @file
 * DomainEditor
 *
 * It contains the definition to:
 * @code
class DomainEditor;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2011
 *
 * @version 1.0.0
 */
namespace Energine\share\components;
use Energine\share\gears\FieldDescription;
/**
 * Domain editor.
 *
 * @code
class DomainEditor;
@endcode
 */
class DomainEditor extends Grid {
    /**
     * @copydoc Grid::__construct
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
     * @copydoc Grid::prepare
     */
    // Изменяем типы филдов
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
        }
    }
    /**
     * @copydoc Grid::defineParams
     */
    // Добавлеям параметр идентификатор сайта
    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            array(
                 'siteID' => false,
            )
        );
    }
    /**
     * @copydoc Grid::saveData
     */
    // Нет смысла создавать отдельный сейвер
    // Проверяем на правильность заполнянеия поля корня сайта
    protected function saveData(){

        if(isset($_POST[$this->getTableName()]['domain_root']) && (substr($_POST[$this->getTableName()]['domain_root'], -1) != '/')){
            $_POST[$this->getTableName()]['domain_root'] .= '/';
        }
        return parent::saveData();
    }
}
