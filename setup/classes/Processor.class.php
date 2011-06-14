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
         * Путь к модулю вывода
         *
         */
        const PATH_SITE = 'site';

        /**
         * Путь к модулям
         *
         */
        const PATH_MODULES = 'modules';

        /**
         * Набор данных
         *
         * @var array
         * @access private
         */
        private $dataset;

        /**
         * данные кофигурации
         *

         * @access private
         */
        private $Config;

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
         * Шаблон файла htaccess для папки uploads
         *
         * @var string
         * @access private
         */
        private $htaccessUploadsTPL;
        
        private $robotsTxtTPL;
        
        /**
         * Путь к шаблону файла htaccess
         *
         * @var string
         * @access private
         */
        private $htaccessTPLPath = 'data/htaccess.sample';
        /**
         * Путь к шаблону файла htaccess uploads
         *
         * @var string
         * @access private
         */
        private $htaccessUploadsTPLPath = 'data/htaccess.uploads.sample';
        private $robotsTxtTPLPath = 'data/robots.txt.sample';
         
        /**
         * Список папок, которые необходимо создать после установки
         *
         * @var array
         * @access private
         */

        private $foldersList = array(
            '/images',
            '/scripts',
            '/stylesheets',
            '/templates',
            '/templates/content',
            '/templates/layout',
            '/templates/icons',
            '/gears',
            '/images/admin',
            '/scripts/admin',
            '/stylesheets/admin',
            '/templates/content/admin',
            '/templates/layout/admin',
            '/templates/icons/admin',
            '/gears/admin',
            '/images/default',
            '/scripts/default',
            '/stylesheets/default',
            '/templates/content/default',
            '/templates/layout/default',
            '/templates/icons/default',
            '/gears/default',
        );


        static public $uploadsFolders = array(
            'public',
            'temp',
            'tmp'
        );
         
        /**
         * Конструктор класса
         *
         * @param array
         * @param SimpleXML
         * @return void
         * @access public
         */
        public function __construct($data,$conf) {

            if (!empty($data) && is_array($data)) {
            	$this->dataset = $data;
            } else {
                throw new Exception('Данные не верны, либо отсутствуют!');
            }

            $this->systemConfigPath = $this->dataset['serverRoot'].'/'.PATH_SYSTEM_CONFIG;

            $this->Config = $conf;

            if (
                !(
	                file_exists($this->htaccessTPLPath)
	                && file_exists($this->htaccessUploadsTPLPath)
	                && ($this->htaccessTPL = file_get_contents($this->htaccessTPLPath))
	                && ($this->htaccessUploadsTPL = file_get_contents($this->htaccessUploadsTPLPath))
	                && ($this->robotsTxtTPL = file_get_contents($this->robotsTxtTPLPath))
                )
            ) {
                throw new Exception('Отстутствуют необходимые шаблоны.');
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

            array_walk($this->foldersList, array($this,'systemFoldersCreate'));
            $this->getViewer()->addBlock('Необходимые папки успешно созданы.',Viewer::TPL_CHECKER_CONFIRM);

/*
*
* тут нужно бы создать конфиг файл и записать в базу имя домена
*
*/

$configFile = "<?php
return array(
    'project' => '".$this->dataset['siteName']."',
    'database' => array(
        'master' => array(
            'dsn' => 'host=".$this->dataset['host'].";port=3306;dbname=".$this->dataset['DBName']."',
            'username' => '".$this->dataset['username']."',
            'password' => '".$this->dataset['password']."'
        )
    ),
    'site' => array(
        'domain' => '".$_SERVER ["HTTP_HOST"]."',
        'debug' => 1,
        'asXML' => 0,
        'compress' => 1,
        'root' => '".$this->dataset['siteRoot']."'
    ),
    'cache' => array(
        'enable' => 0,
        'host' => '127.0.0.1',
        'port' => '11211'
    ),
    'document' => array(
        'transformer' => 'main.xslt',
        'xslcache' => 0,
        'xslprofile' => 0
    ),
    'session' => array(
        'timeout' => 6000,
        'lifespan' => 108000,
    ),
    'mail' => array(
        'from' => 'noreply@energine.org',
        'manager' => 'demo@energine.org',
        'feedback' => 'demo@energine.org'
    ),
    'video' => array(
        'ffmpeg' => '/usr/bin/ffmpeg'
    ),
    'google' => array(
        'verify' => '',
        'analytics' => ''
    )
);
";

$cnf = fopen($this->systemConfigPath, "w");

if (fwrite($cnf, $configFile) === FALSE) {
        echo "Не могу произвести запись в файл ($filename)";
        exit;
    }

fclose($cnf);




            $htaccessPath = $this->dataset['serverRoot'].'/.htaccess';
            $htaccessUploadsProtectedPath = $this->dataset['serverRoot'].'/uploads/temp/.htaccess';
            $htaccessUploadsPrivatePath = $this->dataset['serverRoot'].'/uploads/tmp/.htaccess';
            $robotsTxtPath = $this->dataset['serverRoot'].'/robots.txt';
            
            if (
                !@file_put_contents(
                    $htaccessPath, 
                    str_replace(
                        '#{siteRoot}',
                        $this->dataset['siteRoot'],
                        $this->htaccessTPL
                    )
                )
            ) {
                throw new CheckerException(array('Невозможно создать файл .htaccess! Проверьте уровень прав.','Необходимо изменить права на корневую директорию для продолжения инсталяции.'),Viewer::TPL_ERROR);
            } else {
                $this->getViewer()->addBlock('Файл .htaccess успешно обновлен.',Viewer::TPL_CHECKER_CONFIRM);
            }
            
            if (!@file_put_contents(
	            $htaccessUploadsProtectedPath, 
	            str_replace(
	               array(
	                   '#{siteRoot}',
	                   '#{mode}'
	               ), 
	               array(
	                   $this->dataset['siteRoot'],
	                   '?protected'
	               ), 
	               $this->htaccessUploadsTPL
	           )
            )) {
                throw new CheckerException(array('Невозможно создать файл uploads/temp/.htaccess! Проверьте уровень прав.','Необходимо изменить права на корневую директорию для продолжения инсталяции.'),Viewer::TPL_ERROR);
            } else {
                $this->getViewer()->addBlock('Файл uploads/temp/.htaccess успешно обновлен.',Viewer::TPL_CHECKER_CONFIRM);
            }
            
            if (!@file_put_contents(
                $htaccessUploadsPrivatePath, 
                str_replace(
                   array(
                       '#{siteRoot}',
                       '#{mode}'
                   ), 
                   array(
                       $this->dataset['siteRoot'],
                       ''
                   ), 
                   $this->htaccessUploadsTPL
               )
            )) {
                throw new CheckerException(array('Невозможно создать файл uploads/tmp/.htaccess! Проверьте уровень прав.','Необходимо изменить права на корневую директорию для продолжения инсталяции.'),Viewer::TPL_ERROR);
            } else {
                $this->getViewer()->addBlock('Файл uploads/tmp/.htaccess успешно обновлен.',Viewer::TPL_CHECKER_CONFIRM);
            }
            
            if (!@file_put_contents(
                $robotsTxtPath, 
                str_replace(
                       '#{siteRoot}',
                       'http://'.$_SERVER['SERVER_NAME'].$this->dataset['siteRoot'],
                       $this->robotsTxtTPL
               )
            )) {
                throw new CheckerException(array('Невозможно создать файл robots.txt! Проверьте уровень прав.','Необходимо изменить права на корневую директорию для продолжения инсталяции.'),Viewer::TPL_ERROR);
            } else {
                $this->getViewer()->addBlock('Файл robots.txt успешно обновлен.',Viewer::TPL_CHECKER_CONFIRM);
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

            if (!file_exists($fname) && !@mkdir($fname,CHMOD_DIRS)) {
            	throw new CheckerException(array('Невозможно создать директорию ('.$fname.')!','Необходимо изменить права на корневую директорию для продолжения инсталяции.',Viewer::TPL_ERROR));
            } elseif(!is_writable($fname) && !@chmod($fname,CHMOD_DIRS)) {
                throw new Exception('Невозможно изменить права на директорию ('.$fname.')!');
            }
    	}
    	
    	
    	/*private function changeUploadsPermissions($folderName){
            $fname = $this->dataset['serverRoot'].'/'.UPLOADS_FOLDER_NAME.'/'.$folderName;
            
            if(!@chmod($fname, UPLOADS_CHMOD_DIRS)){
            	throw new Exception('Невозможно изменить права на директорию ('.$fname.')!');
            }
                 		
    	}*/

    }