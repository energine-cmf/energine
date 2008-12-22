<?php
    /**
     * Содержит класс DataChecker
     *
     * @package energine
     * @subpackage configurator
     * @author Tigrenok
     * @copyright ColoCall 2007
     * @version $Id: DataChecker.class.php,v 1.19 2008/04/11 10:31:19 pavka Exp $
     */

    require_once('Model.class.php');

    /**
     * Класс проверки наличия данных и их валидность.
     *
     * @package energine
     * @subpackage configurator
     */
    class DataChecker extends Model {
        /**
         * Переключатель формы
         *
         * @var string
         * @access private
         */
        private $dataset;

        /**
         * Список соответствий названий даных
         *
         * @var array
         * @access private
         */
        private $namespace = array(
            'siteName' => 'Название сайта',
            'siteRoot' => 'Путь от корня сайта',
            'host' => 'MySQL хост',
            'DBName' => 'MySQL имя базы данных',
            'username' => 'MySQL имя пользователя',
            'password' => 'MySQL пароль'
        );

        /**
         * DBConnect instance
         *
         * @var DBConnect instance
         * @access private
         */
        private $sql;

        /**
         * XML данные кофигурации
         *
         * @var SimpleXML
         * @access private
         */
        private $xmlConfig;

        /**
         * Флаг ошибки
         *
         * @var boolean
         * @access private
         */
        private $error;

        /**
         * Конструктор класса
         *
         * @param SimpleXML
         * @return void
         * @access public
         */
        public function __construct($xmlconf) {

            $this->xmlConfig = $xmlconf;

        }

        /**
         * Проверяет наличие данных и их валидность.
         *
         * @param string
         * @return void
         * @access public
         */
        public function run($template=Viewer::TPL_FORM) {

            if(!isset($_POST['install'])) {
               	$this->getViewer()->addBlock('Необходимые данные:',Viewer::TPL_HEADER);
            	$this->getViewer()->addBlock('',$template);
            }
            else {
                $this->dataset = $_POST;
    	        $this->dataset['serverRoot'] = str_replace(SCRIPT_NAME,'',$_SERVER['SCRIPT_FILENAME']);

    	        $this->getViewer()->addBlock('Проверка данных:',Viewer::TPL_HEADER);
    	        $this->prepare();
    	        $this->checkDB();

    	        if ($this->error) {
    	        	throw new CheckerException();
    	        }
    	        else {
    	            $this->getViewer()->addBlock('Все данные верны. Можно продолжать установку.',Viewer::TPL_CHECKER_CONFIRM);
    	        }
            }
        }

        /**
    	 * Проверяет и обрабатывает принятые данные
    	 *
    	 * @return void
    	 * @access private
    	 */
        private function prepare() {

            if(empty($this->dataset)) {
                throw new Exception('Нет данных!');
            }

            foreach ($this->dataset as $key => $value) {

                if($key == 'siteRoot' && !preg_match("/.*\/$/",$value)) {$value .= '/';}
                if($key == 'siteRoot' &&!preg_match("/^\/.*/",$value)) {$value = '/'.$value;}

            	if (empty($value)) {
            	    $this->getViewer()->addBlock('Поле '.$key.' не может быть пустым!',Viewer::TPL_CHECKER_EXCEPTION);
            	    $this->error = true;
            	}
            	else {
            	    $value = str_replace("`", "'", $value);
                    $this->dataset[$key] = trim(htmlspecialchars($value));
            	}
            }
        }

        /**
    	 * Проверяет возможность доступа к БД по принятым данным
    	 *
    	 * @return void
    	 * @access private
    	 */
        private function checkDB() {

            if(empty($this->dataset)) {
                throw new Exception('Нет данных!');
            }

            if (!isset($this->dataset['host'])) {
                $this->dataset['host'] = $this->xmlConfig->database->host;
                $this->dataset['DBName'] = $this->xmlConfig->database->name;
                $this->dataset['username'] = $this->xmlConfig->database->username;
            }

            $this->sql = DBConnect::run();

            if (!@($link = $this->sql->connect($this->dataset['host'], $this->dataset['username'], $this->dataset['password']))) {
                $this->getViewer()->addBlock(array('Невозможно подключиться к базе данных! (DataChecker)',$this->sql->error()),Viewer::TPL_CHECKER_EXCEPTION);
                $this->error = true;
            }
            else {
                $this->getViewer()->addBlock('Подключение к базе данных прошло успешно!',Viewer::TPL_CHECKER_CONFIRM);
            }

            if ($link && !@$this->sql->selectdb($this->dataset['DBName'])) {
                $this->getViewer()->addBlock(array('Невозможно выбрать указанную базу данных!',$this->sql->error()),Viewer::TPL_CHECKER_EXCEPTION);
                $this->error = true;
            }
            elseif($link) {
                $this->getViewer()->addBlock('Выбор указанной базы данных прошел успешно!',Viewer::TPL_CHECKER_CONFIRM);
            }

            if($link) {
                $this->sql->query('CREATE TABLE IF NOT EXISTS `test_table` (`smap_id` int(10) unsigned NOT NULL default \'0\') ENGINE=InnoDB DEFAULT CHARSET=utf8;');
                $this->sql->query('SHOW TABLE STATUS FROM '.$this->dataset['DBName'].' LIKE \'test_table\'');
                $test_table = $this->sql->fetchHash();

                if ($test_table[0]['Engine'] != 'InnoDB') {
                	$this->getViewer()->addBlock(array('База данных не поддерживает InnoDB!',$test_table),Viewer::TPL_CHECKER_EXCEPTION);
                	$this->error = true;
                }
                else {
                    $this->getViewer()->addBlock('База данных поддреживает InnoDB!',Viewer::TPL_CHECKER_CONFIRM);
                }

                $this->sql->query('DROP TABLE IF EXISTS `test_table`;');
            }

            @$this->sql->disconnect();

        }

        /**
    	 * Возвращает обработанные данные
    	 *
    	 * @return array
    	 * @access public
    	 */
        public function getData() {
            return $this->dataset;
        }


        /**
    	 * Возвращает объект конфигурации
    	 *
    	 * @return SimpleXML
    	 * @access public
    	 */
        public function getXMLConfig() {
            return $this->xmlConfig;
        }

    }