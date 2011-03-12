<?php

/**
 * Класс SystemException
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 * @copyright Energine 2006
 */

/**
 * Базовое исключение.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 */
class SystemException extends Exception {
    /**
     * Критическая ошибка
     */
    const ERR_CRITICAL = 0;

    /**
     * Ошибка 404 - страницы не существует
     */
    const ERR_404 = 1;

    /**
     * Ошибка 403 - нет прав на просмотр страницы
     */
    const ERR_403 = 2;

    /**
     * Ошибка при работе с БД
     */
    const ERR_DB = 3;

    /**
     * Ошибка разработчика, где-то что-то неверно написано :)
     */
    const ERR_DEVELOPER = 4;

    /**
     * Предупреждение
     */
    const ERR_WARNING = 10;

    /**
     * Замечание
     */
    const ERR_NOTICE = 20;

    /**
     * Ошибка, связанная с мультиязычностью. Возникает при обработке другой
     * ошибки и отсутствия для неё переводов. Без ERR_LANG возможет уход
     * в рекурсию и полный пиздец.
     *
     * Данная ошибка касается исключительно разработчиков системы.
     *
     * @todo сделать хоть что-нибудь! :)
     */
    const ERR_LANG = 5;


    /**
     * @access protected
     * @var Response экземпляр объекта Response
     */
    protected $response;



    /**
     * @access protected
     * @var mixed дополнительная информация об ошибке
     */
    protected $customMessages = array();

    /**
     * Конструктор класса.
     *
     * @access public
     * @param string $message
     * @param int $code
     * @param mixed $customMessages
     * @return void
     * @todo определиться с $customMessages: это mixed или array?
     */
	public function __construct($message, $code = self::ERR_CRITICAL, $customMessages = null) {
        $this->response = E()->getResponse();
        if (isset($customMessages)) {
            if (!is_array($customMessages)) {
            	$this->customMessages = array($customMessages);
	}
            else {
                $this->customMessages = $customMessages;
}
        }


        
        if ($code == self::ERR_LANG) {
            $this->response->setStatus(503);
            $this->response->setHeader('Retry-After', 20);
            $message = DBWorker::_translate($message, E()->getLanguage()->getDefault());
        }
        elseif ($code == self::ERR_403) {
        	$this->response->setStatus(403);
            $message = DBWorker::_translate($message, E()->getLanguage()->getCurrent());
        }
        elseif ($code == self::ERR_404) {
        	$this->response->setStatus(404);
            $message = DBWorker::_translate($message, E()->getLanguage()->getCurrent());
        }
        elseif ($code != self::ERR_DB ) {
            $message = DBWorker::_translate($message, E()->getLanguage()->getCurrent());
        }
        /*else {
            $this->response->setStatus(503);
            $this->response->setHeader('Retry-After', 20);
        }*/
        /*elseif ($code != self::ERR_DB ) {
            $message = DBWorker::_translate($message, E()->getLanguage()->getCurrent());
        }*/


        parent::__construct($message, $code);
    }

/**
	 * Возвращает дополнительную информацию об ошибке.
 *
	 * @access public
	 * @return array
	 * @todo переименовать в getCustomMessages
 */
    public function getCustomMessage() {
        return $this->customMessages;
    }

    /**
     * Используется для принудительного изменения месторасположения ошибки
     * Вызывается в nrgnErrorHandler
     *
     * @param  string $file
     * @return SystemException
     */
    public function setFile($file){
        $this->file = $file;
        return $this;
    }
    /**
     *
     * @param  $line
     * @return SystemException
     * @see SystemException::setFile
     */
    public function setLine($line){
        $this->line = $line;
        return $this;
    }


}

