<?php
    /**
     * Содержит класс CheckerException
     *
     * @package energine
     * @subpackage configurator
     * @author Tigrenok
     * @copyright Energine 2007
     * @version $Id: CheckerException.class.php,v 1.2 2007/11/05 17:55:09 tigrenok Exp $
     */

    /**
     * Создает исключения проверок
     *
     * @package energine
     * @subpackage configurator
     */
    class CheckerException extends Exception {
        /**
         * Шаблон вывода
         *
         * @var string
         * @access private
         */
        private $template = Viewer::TPL_CHECKER_EXCEPTION;

        /**
         * Конструктор класса
         *
         * @param mixed сообщение об ошибке или массив сообщений
         * @return void
         * @access public
         */
        public function __construct($message='',$template='') {
            parent::__construct();
			$this->message = $message;
            if ($template) {
                $this->template = $template;
            }
        }

        /**
         * Возвращает установленный шаблон
         *
         * @return string
         * @access public
         */
        public function getTPL() {
            return $this->template;
        }
    }
