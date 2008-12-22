<?php
    /**
     * Содержит класс Viewer
     *
     * @package energine
     * @subpackage configurator
     * @author Tigrenok
     * @copyright ColoCall 2007
     * @version $Id: Viewer.class.php,v 1.5 2007/11/12 15:15:56 tigrenok Exp $
     */

    /**
     * Выводит результат работы скрипта
     *
     * @package energine
     * @subpackage configurator
     */
    class Viewer {

        /**
         * Шаблон вывода обвязки страницы
         *
         */
        const TPL_HTML = 'tpl/install.tpl';

        /**
         * Шаблон вывода поля формы
         *
         */
        const TPL_DEFAULT = 'tpl/default.tpl';

        /**
         * Шаблон вывода загловков
         *
         */
        const TPL_HEADER = 'tpl/header.tpl';

        /**
         * Шаблон вывода последнего загловка
         *
         */
        const TPL_FOOTER = 'tpl/footer.tpl';

        /**
         * Шаблон вывода ошибки
         *
         */
        const TPL_ERROR = 'tpl/error.tpl';

        /**
         * Шаблон вывода исключений чекера
         *
         */
        const TPL_CHECKER_EXCEPTION = 'tpl/checkerexception.tpl';

        /**
         * Шаблон вывода подтверждений чекера
         *
         */
        const TPL_CHECKER_CONFIRM = 'tpl/checkerconfirm.tpl';

        /**
         * Шаблон вывода скриптов линкера
         *
         */
        const TPL_LINKER_SCRIPT = 'tpl/linkerscript.tpl';

        /**
         * Шаблон вывода подтверждений линкера
         *
         */
        const TPL_LINKER_CONFIRM = 'tpl/linkerconfirm.tpl';

        /**
         * Шаблон вывода подтверждения
         *
         */
        const TPL_CONFIRM = 'tpl/confirm.tpl';

        /**
         * Шаблон вывода формы
         *
         */
        const TPL_FORM = 'tpl/form.tpl';

        /**
         * Шаблон вывода формы
         *
         */
        const TPL_SQLFORM = 'tpl/sqlform.tpl';

        /**
         * Шаблон вывода формы
         *
         */
        const TPL_SQLDFORM = 'tpl/sqldform.tpl';

        /**
         * Шаблон вывода формы
         *
         */
        const TPL_USERFORM = 'tpl/userform.tpl';

        /**
         * Шаблон вывода формы
         *
         */
        const TPL_VIEWOPTS = 'tpl/viewoptions.tpl';

        /**
         * Данные для вывода
         *
         * @var array
         * @access private
         */
        private $blocks = array();

        /**
    	 * Конструктор класса
    	 *
    	 * @return void
    	 * @access public
    	 */
    	public function __construct() {

    	    if(!file_exists(self::TPL_HTML)) {
                die('Шаблон '.self::TPL_HTML.' отсутствует!');
            }

    	}

        /**
         * Добавляет блок вывода
         *
         * @param mixed данные для вывода
         * @param string имя шаблона
         * @return void
         * @access public
         */
        public function addBlock($outData,$tplName = self::TPL_DEFAULT) {
            if(file_exists($tplName)) {
                ob_start();
                include($tplName);
                $this->blocks[] = ob_get_contents();
                ob_end_clean();
            } else {
                die('Шаблон '.$tplName.' отсутствует!');
            }
        }

        /**
         * Выводит результат работы на экран
         *
         * @return void
         * @access public
         */
        public function printResult() {

            $outputTPL = file_get_contents(self::TPL_HTML);
        	echo str_replace('^!',implode("",$this->blocks),$outputTPL);

        }

    }