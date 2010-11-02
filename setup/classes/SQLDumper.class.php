<?php
    /**
     * Содержит класс SQLDumper
     *
     * @package energine
     * @subpackage configurator
     * @author Tigrenok
     * @copyright Energine 2007

     */

    require_once('Model.class.php');
    require_once('DBConnect.class.php');

    /**
     * Выводит результат работы скрипта
     *
     * @package energine
     * @subpackage configurator
     */
    class SQLDumper extends Model {
    	/**
         * Массив запросов
         *
         * @var array
         * @access private
         */
        private $sql_queries;

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
         * DBConnect instance
         *
         * @var DBConnect instance
         * @access private
         */
        private $sql;

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
            }
            else {
                throw new Exception('Данные не верны, либо отсутствуют!');
            }

            $this->xmlConfig = $xmlconf;

        }

        /**
         * Запускает модель
         *
         * @return void
         * @access public
         */
        public function run() {

            $this->sql = DBConnect::run();
            $this->sql->connect($this->dataset['host'],$this->dataset['username'],$this->dataset['password']);
            $this->sql->selectdb($this->dataset['DBName']);

            if (isset($this->dataset['restoreDBR']) && $this->dataset['restoreDBR'] == 1) {
               $this->getViewer()->addBlock('Восстановление базы данных из стандартного файла:',Viewer::TPL_HEADER);
               $this->parseDump(PATH_SQL_DUMP);
            }
            elseif (
                    isset($this->dataset['restoreDBR'])
                    && $this->dataset['restoreDBR'] == 2
                    && isset($_FILES['dumpfile'])
                    ) {
                $this->getViewer()->addBlock('Восстановление базы данных из закачанного файла:',Viewer::TPL_HEADER);
                $this->parseDump($_FILES['dumpfile']['tmp_name']);
            } elseif(isset($this->dataset['createDump'])) {
                $this->createDump();
            }

            if (!empty($this->sql_queries)) {
                $this->restoreDB();
            }

            if (isset($this->dataset['admMail'])) {
        		$this->sql->query("INSERT INTO `user_Users` (`u_name`, `u_password`, `u_is_active`) VALUES ('".$this->dataset['admMail']."', '".sha1($this->dataset['admPassword'])."', 1)");
        		$this->sql->query("INSERT INTO `user_UserGroups` (`u_id`, `group_id`) VALUES (".$this->sql->lastId().", 1)");
        		$this->sql->query('SET AUTOCOMMIT=1;');

        		$this->getViewer()->addBlock('Новый пользователь ('.$this->dataset['admMail'].') успешно создан.',Viewer::TPL_CHECKER_CONFIRM);
    		}

            $this->sql->disconnect();

        }

        /**
         * Парсит файл дампа
         *
         * @param string Путь к файлу дампа
         * @return void
         * @access private
         */
        private function parseDump($sqlDumpPath) {

            if (!file_exists($sqlDumpPath)) {
      		    throw new Exception('Файл с дампом базы ('.$sqlDumpPath.') не существует!');
        	}
        	elseif(!@($fa = file($sqlDumpPath))) {
        	    throw new Exception('Невозможно прочесть файл с дампом базы ('.$sqlDumpPath.')!');
        	}

        	$sqlquery = '';
    		foreach ($fa as $value) {
    		    $value = trim($value);
    		    if (!empty($value) && !preg_match("/^(#|--)/", $value) && preg_match("/;$/", $value)) {
                    $this->sql_queries[] = $sqlquery.$value;
    		        $sqlquery = '';
            	}
            	elseif (!empty($value) && !preg_match("/^(#|--)/", $value)) {
            	    $sqlquery .= $value.' ';
            	}
    		}

    		$this->getViewer()->addBlock('Файл дампа успешно обработан.',Viewer::TPL_CHECKER_CONFIRM);
    		//$this->getViewer()->addBlock(array('Нажмите, чтобы показать список запросов'=>$this->sql_queries),Viewer::TPL_LINKER_CONFIRM);
        }

        /**
    	 * Восстанавливает базу из дампа
    	 *
    	 * @return void
    	 * @access private
    	 */
    	private function restoreDB() {
    		$this->sql->query("SET NAMES '".FORCED_CHARSET."'");
    		foreach ($this->sql_queries as $query) {
    		    $this->sql->query($query);
    		}
    		$this->getViewer()->addBlock('База данных успешно восстановлена.',Viewer::TPL_CHECKER_CONFIRM);
    	}

    	/**
    	 * Создаёт дамп базы
    	 *
    	 * @return void
    	 * @access public
    	 */
    	public function createDump() {
    		$dumpstring = 'SET FOREIGN_KEY_CHECKS=0;';
            $dumpstring .= "\n";
            $dumpstring .= 'SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";';
            $dumpstring .= "\n\n";
            $dumpstring .= 'SET AUTOCOMMIT=0;';
            $dumpstring .= "\n";
            $dumpstring .= 'START TRANSACTION;';
            $dumpstring .= "\n\n";

            $this->sql->query("SET NAMES '".FORCED_CHARSET."'");
            /* собираем информацию о таблицах */
            $this->sql->query("SHOW TABLE STATUS FROM `".$this->dataset['DBName']."`");

            $tbls = $this->sql->fetchHash();

            foreach ($tbls as $line) {

                $dumpstring .= "DROP TABLE IF EXISTS `".$line['Name']."`;";
                $dumpstring .= "\n";

                $this->sql->query("SHOW CREATE TABLE ".$line['Name']);
                $subline = $this->sql->fetchArray();

                $dumpstring .= $subline[0][1];
                $dumpstring .= ";\n\n";

                /* собираем информацию о колонках */
                $this->sql->query("SHOW COLUMNS FROM ".$line['Name']);
                $names = array();
                $columns = $this->sql->fetchHash();
                foreach ($columns as $subline) {
                    $names[] = '`'.$subline['Field'].'`';
                }
                $column_names = implode(',',$names);

                $this->sql->query("SELECT * FROM ".$line['Name']);
                $values = array();

                $column_values = $this->sql->fetchHash();
                foreach ($column_values as $subline) {
                    array_walk($subline, array($this->sql,'prepare'));
                    $values[] = '('.implode(',',$subline).')';
                }

                $dumpstring .= 'INSERT INTO `'.$line['Name'].'` ('.$column_names.') VALUES'."\n";
                $dumpstring .= implode(",\n",$values).";\n";

            }

            $dumpstring .= 'SET FOREIGN_KEY_CHECKS=1;';
            $dumpstring .= "\n";
            $dumpstring .= 'COMMIT;';

            $projectname = $this->xmlConfig->project->name;
            $projectname = stripslashes(strtolower($projectname));
        	$projectname = preg_replace("/\]/", " ", $projectname);
        	$projectname = preg_replace("/\[/", " ", $projectname);
        	$projectname = preg_replace("/\\\/", " ", $projectname);
        	$projectname = preg_replace("/[^.a-zA-Zа-яА-Я0-9_]/", " ", $projectname);
        	$projectname = preg_replace("/\s+/", "_", $projectname);
        	$projectname = trim($projectname, "_");

            header("Content-Type: text/x-sql; charset=".FORCED_CHARSET);
	        header('Content-Disposition: attachment; filename="'.$projectname.'_sql_dump'.date('_Y-m-d_H-i-s').'.sql"; charset='.FORCED_CHARSET);
            print_r($dumpstring);
            die;
    	}

    }