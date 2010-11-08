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
 * Список виджетов (компонентов)
 *
 * @package energine
 * @subpackage share
 * @author spacelord
 */


class WidgetsRepository extends Grid {
    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module

     * @param array $params
     * @access public
     */
	public function __construct($name, $module,   array $params = null) {
        parent::__construct($name, $module,  $params);
        $this->setTableName('share_widgets');
	}

}