<?php

/**
 * Класс SystemExceptionm, DummyException.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 * @copyright Energine 2006
 * @version $Id$
 */

/**
 * Системное исключение.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 */
class SystemException extends BaseException {

    /**
     * Конструктор класса.
     *
     * @access public
     * @param string $message
     * @param int $code
     * @param mixed $customMessages
     * @return void
     */
	public function __construct($message, $code = self::ERR_CRITICAL, $customMessages = null) {
		parent::__construct($message, $code, $customMessages);
	}
}

/**
 * Фиктивное исключение.
 * Используется при необходимости прерывания нормального выполнения программы
 * и выхода в обработчик исключений.
 *
 * @package energine
 * @subpackage core
 * @see Component::generateErrors()
 */
class DummyException extends Exception {};
