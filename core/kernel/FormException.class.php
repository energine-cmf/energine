<?php

/**
 * Класс FormException.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 * @copyright Energine 2006
 * @version $Id$
 */


/**
 * Исключение формы.
 * Используется при ошибке сохранения данных формы.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 */
class FormException extends BaseException {

    /**
     * Конструктор класса.
     *
     * @access public
     * @return void
     */
	public function __construct() {
		parent::__construct('Dummy Message', self::ERR_NOTICE);
	}
}
