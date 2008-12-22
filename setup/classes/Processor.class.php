<?php
    /**
     * Содержит класс Processor
     *
     * @package energine
     * @subpackage configurator
     * @author Tigrenok
     * @copyright ColoCall 2007
     * @version $Id: Processor.class.php,v 1.6 2008/08/01 14:21:06 pavka Exp $
     */

    require_once('Model.class.php');

    /**
     * Записывает данные в файлы конфигурации
     *
     * @package energine
     * @subpackage configurator
     */
    class Processor extends Model {
        /**
         * Набор данных
         *
         * @var array
         * @access private
         */
        private $dataset;

        /**
         * XML данные кофигурации
         *
         * @var SimpleXML
         * @access private
         */
        private $xmlConfig;

        /**
         * Путь к файлу конфигурации
         *
         * @var string
         * @access private
         */
        private $systemConfigPath;

        /**
         * Шаблон файла htaccess
         *
         * @var string
         * @access private
         */
        private $htaccessTPL;

        /**
         * Путь к шаблон файла htaccess
         *
         * @var string
         * @access private
         */
        private $htaccessTPLPath = 'data/htaccess.sample';

        /**
         * Список папок, которые необходимо создать после установки
         *
         * @var array
         * @access private
         */
        private $foldersList = array(
            '/images',
            '/logs',
            '/scripts',
            '/stylesheets',
            '/templates',
            '/templates/content',
            '/templates/layout',
            '/tmp',
			'/flash',
            //'/uploads'
        );

        /**
         * Конструктор класса
         *
         * @param array
         * @param SimpleXML
         * @return void
         * @access public
         */
        public function __construct($data,$xmlconf) {

            if (!empty($data) && is_array($data)) {
            	$this->dataset = $data;
            } else {
                throw new Exception('Данные не верны, либо отсутствуют!');
            }

            $this->systemConfigPath = $this->dataset['serverRoot'].'/'.PATH_SYSTEM_CONFIG;

            $this->xmlConfig = $xmlconf;

            if (!file_exists($this->htaccessTPLPath) || !($this->htaccessTPL = file_get_contents($this->htaccessTPLPath))) {
                throw new Exception('Шаблон файла htaccess отсутствует!');
            }
        }


        /**
         * Запускает модель
         *
         * @return void
         * @access public
         */
        public function run() {

            $this->getViewer()->addBlock('Работа с данными:',Viewer::TPL_HEADER);

            array_walk($this->foldersList,array($this,'systemFoldersCreate'));

            $this->getViewer()->addBlock('Необходимые папки успешно созданы.',Viewer::TPL_CHECKER_CONFIRM);

            $this->xmlConfig->project->name = $this->dataset['siteName'];
            $this->xmlConfig->database->host = $this->dataset['host'];
            $this->xmlConfig->database->name = $this->dataset['DBName'];
            $this->xmlConfig->database->username = $this->dataset['username'];
            $this->xmlConfig->database->password = $this->dataset['password'];
            $this->xmlConfig->site->root = $this->dataset['siteRoot'];

            if (!($this->xmlConfig->asXML($this->systemConfigPath))) {
                throw new Exception('Невозможно произвести запись в файл конфигурации!');
            } else {
                $this->getViewer()->addBlock('Файл конфигурации успешно обновлен.',Viewer::TPL_CHECKER_CONFIRM);
            }

            $htaccessPath = $this->dataset['serverRoot'].'/.htaccess';

            if (!@file_put_contents($htaccessPath,str_replace('#{siteRoot}',$this->dataset['siteRoot'],$this->htaccessTPL))) {
                throw new CheckerException(array('Невозможно создать файл .htaccess! Проверте права.','Необходимо изменить права на корневую директорию для продолжения инсталяции.'),Viewer::TPL_ERROR);
            } else {
                $this->getViewer()->addBlock('Файл .htaccess успешно обновлен.',Viewer::TPL_CHECKER_CONFIRM);
            }

        }


        /**
    	 * Создаёт системные папки и расставляет права
    	 *
    	 * @param string имя папки
    	 * @param string порядковй номер
    	 * @return void
    	 * @access private
    	 */
    	private function systemFoldersCreate($folder) {
    	    $fname = $this->dataset['serverRoot'].$folder;

            if (!file_exists($fname) && !@mkdir($fname,0755)) {
            	throw new CheckerException(array('Невозможно создать директорию ('.$fname.')!','Необходимо изменить права на корневую директорию для продолжения инсталяции.',Viewer::TPL_ERROR));
            } elseif(!is_writable($fname) && !@chmod($fname,0755)) {
                throw new Exception('Невозможно изменить права на директорию ('.$fname.')!');
            }
    	}

    }