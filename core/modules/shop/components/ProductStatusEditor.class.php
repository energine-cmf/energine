<?php

/**
 * Содержит класс ProductStatusEditor
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 * @copyright ColoCall 2007
 * @version $Id$
 */

//require_once('core/modules/share/components/Grid.class.php');

/**
 * Редактор статусов продуктов
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 */
class ProductStatusEditor extends Grid {
    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module
     * @param Document $document
     * @param array $params
     * @access public
     */
    public function __construct($name, $module, Document $document, array $params = null) {
        parent::__construct($name, $module, $document, $params);
        $this->setTableName('shop_product_statuses');
    }

    /**
	 * Дизейблится дефолтное значение
	 *
	 * @return void
	 * @access protected
	 */

    protected function edit() {
        parent::edit();
        if($this->getData()->getFieldByName('ps_is_default')->getRowData(0) === true) {
            $this->getDataDescription()->getFieldDescriptionByName('ps_is_default')->setMode(FieldDescription::FIELD_MODE_READ);
        }
    }
    /**
     * При добавлении віставляем уровень прав в минимально возможный видимый
     *
     * @return void
     * @access protected
     */

     protected function add() {
        parent::add();
        $this->getData()->getFieldByName('right_id')->setData(FieldDescription::FIELD_MODE_READ);
        $this->getData()->getFieldByName('ps_is_visible')->setData(true);
     }

    /**
	  * Снимаем признак дефолтного значения
	  *
	  * @return mixed
	  * @access protected
	  */

    protected function saveData() {
        if (isset($_POST[$this->getTableName()]['ps_is_default']) && $_POST[$this->getTableName()]['ps_is_default']) {
            $this->dbh->modify(QAL::UPDATE, $this->getTableName(), array('ps_is_default'=>0), array('ps_is_default'=>1));
        }
        parent::saveData();
    }

    /**
     * Не даем удалить дефолтную запись
     *
     * @return void
     * @access protected
     */

    protected function deleteData($id) {
        if ($this->dbh->select($this->getTableName(), array('ps_id'), array('ps_is_default'=>1, 'ps_id'=>$id)) !== true) {
            throw new SystemException('ERR_DEFAULT_STATUS', SystemException::ERR_CRITICAL);
        }
        parent::deleteData($id);
    }

    /**
     * Возвращает перечень статусов товаров видимых для пользователя  с заданным уровнем прав
     *
     * @param int уровень прав
     * @return array
     * @access public
     * @static
     */

    static public function getVisibleStatuses($rightsLevel) {
        return simplifyDBResult(DBWorker::$dbhInstance->select('shop_product_statuses', 'ps_id', 'right_id <= '.$rightsLevel),'ps_id');
    }

    /**
     * Возвращает идентификатор дефолтного статуса
     *
     * @return int
     * @access public
     * @static
     */

    static public function getDefaultStatus() {
        return simplifyDBResult(DBWorker::$dbhInstance->select('shop_product_statuses', 'ps_id', array('ps_is_default'=>1)), 'ps_id', true);
    }
}