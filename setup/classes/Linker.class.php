<?php
    /**
     * Содержит класс Linker
     *
     * @package energine
     * @subpackage configurator
     * @author Tigrenok
     * @copyright ColoCall 2007
     * @version $Id: Linker.class.php,v 1.14 2008/08/05 09:48:49 pavka Exp $
     */

    require_once('Model.class.php');

    /**
     * Создает копии (или симлинки) файлов
     *
     * @package energine
     * @subpackage configurator
     */
    class Linker extends Model {
        /**
         * Путь к ядру
         *
         */
        const PATH_CORE = '../core2a';

        /**
         * Название папки со ядром
         *
         */
        const FOLDER_CORE = 'core';

        /**
         * Путь к модулю вывода
         *
         */
        const PATH_SITE = 'site';

        /**
         * Путь к модулям ядра
         *
         */
        const PATH_MODULES = 'modules';

        /**
         * Список обрабатываемых папок модулей
         *
         * @var array
         * @access private
         */
        private $deFoldersList = array(
            'images',
            'scripts',
            'stylesheets',
            'templates/content',
            'templates/layout'
        );

        /**
         * Флаг системы *nix = true, windows = false
         *
         * @var boolean
         * @access private
         */
        private $isNix;

        /**
         * Путь от корня сервера
         *
         * @var string
         * @access private
         */
        private $serverRoot;

        /**
         * Список модулей
         *
         * @var array
         * @access private
         */
        private $modulesList;

        /**
         * Список удалённых при очистке файлов
         *
         * @var array
         * @access private
         */
        private $killedLog = array();

        /**
         * Список скопированных файлов
         *
         * @var array
         * @access private
         */
        private $copyLog;

        /**
         * Список скопированных файлов (или симлинков для *nix систем)
         *
         * @var array
         * @access private
         */
        private $symLinkLog;

        /**
         * Структура папок для копирования (или создания симлинков для *nix систем)
         *
         * @var array
         * @access private
         */
        private $foldersList;

        /**
         * Структура файлов для копирования (или создания симлинков для *nix систем)
         *
         * @var array
         * @access private
         */
        private $filesList;

        /**
         * Конструктор класса
         *
         * @return void
         */
    	public function __construct() {

    	    $this->serverRoot = str_replace(SCRIPT_NAME,'',$_SERVER['SCRIPT_FILENAME']).'/';

    	    $this->isNix = true;
    	    if (isset($_SERVER["WINDIR"])) {
               $this->isNix = false;
    	    }

    	}

        /**
         * Запускает модель
         *
         * @return void
         * @access public
         */
        public function run() {

            $this->getViewer()->addBlock('',Viewer::TPL_LINKER_SCRIPT);

            $this->getViewer()->addBlock('Линкер:',Viewer::TPL_HEADER);

            $this->cleanDestinationFolders();
    		$this->getViewer()->addBlock(array('Нажмите, чтобы показать список удалённых файлов.'=>$this->killedLog),Viewer::TPL_LINKER_CONFIRM);

    		$this->createSymLinks();
    		$this->getViewer()->addBlock(array('Нажмите, чтобы показать список скопированных файлов.'=>$this->copyLog),Viewer::TPL_LINKER_CONFIRM);

    		$this->getViewer()->addBlock('Ура! Процесс установки успешно завершён! :)',Viewer::TPL_FOOTER);

        }

        /**
   		 * Создает список модулей
   		 *
   		 * @param array Список исключений
   		 * @return array Список модулей
   		 * @access private
   		 */
   		private function geatherModulesList($exceptions=array()) {

   		    $dir = $this->serverRoot.self::FOLDER_CORE.'/'.self::PATH_MODULES;

   		    $modulesList = array();

   		    if(!@($handle = opendir($dir))) {
                throw new Exception('Невозможно открыть директорию ('.$dir.')!');
	    	}
   		    while (false !== ($file = readdir($handle))) {
       		    if (!preg_match('/^\./',$file) && !in_array($file,$exceptions)) {
               		$modulesList[] = $dir.'/'.$file;
       		    }
   		    }

   		    closedir($handle);

   		    return $modulesList;
   		}

   		/**
    	 * Собирает пути к папкам и файлам, которые нужно скопировать (или расставить симлинки для *nix систем)
    	 *
    	 * @param string Путь к папке, которую следует обработать.
    	 * @param string Путь к папке, в которую следует скопировать все из исходной.
    	 * @param array Список исключений, имена папок и файлов, которые следует игнорировать
    	 * @return void
    	 * @access private
    	 */
    	private function geatherFileLinks($dir,$destdir,$exceptions=array()) {

    	    if (!@($handle = opendir($dir))) {
                throw new Exception('Невозможно открыть директорию ('.$dir.')!');
            }
            while (false !== ($file = @readdir($handle))) {

            	if (!preg_match('/^\./',$file) && !in_array($file,$exceptions)) {

            		$curfile = $dir.'/'.$file;

                	if (is_dir($curfile)) {
                	    $this->foldersList[$destdir.'/'.$file] = $curfile;
                	    if (!$this->isNix) {
                	    	$this->geatherFileLinks($curfile,$destdir.'/'.$file,$exceptions);
                	    }
                    } else {
                        $this->filesList[$destdir.'/'.$file] = $curfile;
                    }
                }
            }
            @closedir($handle);
   		}

   		/**
    	 * Очищает папки сборки
    	 *
    	 * @return void
    	 * @access private
    	 */
    	private function cleanDestinationFolders() {

			foreach ($this->deFoldersList as $value) {
				$this->killFiles($this->serverRoot.$value,array('CVS'));
			}

			if (file_exists($this->serverRoot.self::FOLDER_CORE) && !$this->isNix) {
			    $this->killFiles($this->serverRoot.self::FOLDER_CORE);
			}

       	}

    	 /**
    	 * Убивает файлы и папки
    	 *
    	 * @param string Путь к папке, которую следует очистить
    	 * @param array Список исключений, имена папок и файлов, которые убивать нельзя
    	 * @return void
    	 * @access private
    	 */
    	private function killFiles($dir,$exceptions=array()) {

    	    if(!@($handle = opendir($dir))) {
    	        throw new Exception('Невозможно открыть директорию ('.$dir.')!');
    	    }
            while (false !== ($file = readdir($handle))) {

            	if (!preg_match('/^\./',$file) && !in_array($file,$exceptions)) {

            		$curfile = $dir.'/'.$file;

                	if (is_dir($curfile) && !$this->isNix) {

            	        $this->killFiles($curfile,$exceptions);
            	        if(!@rmdir($curfile)) {
                	        throw new Exception('Невозможно удалить директорию ('.$curfile.')!');
                	    }

                    } else {
                        if(!@unlink($curfile)) {
                    	        throw new Exception('Невозможно удалить файл ('.$curfile.')!');
                    	    }
                        $this->killedLog[] = $dir.'/'.$file;
                    }
                }
            }
            closedir($handle);
   		}

   		/**
    	 * Создаёт копии файлов ядра, либо делает симлинки (для *nix систем)
    	 *
    	 * @return void
    	 * @access private
    	 */
    	private function createSymLinks() {
			/*
    	    if ($this->isNix) {

    	        if(!file_exists($this->serverRoot.self::FOLDER_CORE) && !@symlink($this->serverRoot.self::PATH_CORE,$this->serverRoot.self::FOLDER_CORE)) {
                    throw new Exception('Невозможно создать символическую ссылку ('.$this->serverRoot.self::PATH_CORE.' => '.$this->serverRoot.self::FOLDER_CORE.')!');
                }

    	    } else {

    	        // тут вызывается собиратель ссылок для ядра для виндовой платформы, в юниксе этого не нужно - просто симлинка делается 
    	    	$this->geatherFileLinks($this->serverRoot.self::PATH_CORE,$this->serverRoot.self::FOLDER_CORE,array('CVS'));
    	    	if(!file_exists($this->serverRoot.self::FOLDER_CORE) && !@mkdir($this->serverRoot.self::FOLDER_CORE)) {
                    throw new Exception('Невозможно создать директорию ('.$this->serverRoot.self::PATH_CORE.')!');
    	    	}
    	    }
			*/
    		
    	    $modulesPathes = $this->geatherModulesList(array('CVS'));
    	    array_push($modulesPathes,$this->serverRoot.self::PATH_SITE);

    	    foreach ($modulesPathes as $value) {
    	        foreach ($this->deFoldersList as $subvalue) {
    	        	$this->geatherFileLinks($value.'/'.$subvalue, $this->serverRoot.$subvalue, array('CVS'));
    	        }
    	    }

	    	if ($this->isNix) {
	    		$this->symLinkFiles();
	    	}
	    	else {
	    	    $this->copyFiles();
	    	}
    	}

        /**
   		 * Копирует файло.
   		 *
   		 * @return void
   		 * @access private
   		 */
   		private function copyFiles() {

   		    foreach (array_keys($this->foldersList) as $destdir) {
                if (!file_exists($destdir)) {
                    $this->copyLog[] = $destdir;
                	if (!@mkdir($destdir)) {
                        throw new Exception('Невозможно создать директорию ('.$destdir.')!');
                    }
                }
   		    }

   		    foreach ($this->filesList as $destfile => $sourfile) {
  		        $this->copyLog[] = $sourfile.' => '.$destfile;
   		    	if (!@copy($sourfile,$destfile)) {
                    throw new Exception('Невозможно скопировать файл ('.$sourfile.' => '.$destfile.')!');
                }
   		    }

   		}

   		/**
   		 * Симлинкует файло. :)
   		 *
   		 * @return void
   		 * @access private
   		 */
   		private function symLinkFiles() {

   		    foreach ($this->foldersList as $destdir => $sourdir) {
   		        $sourdir = realpath($sourdir);
                $this->copyLog[] = $sourdir.' => '.$destdir;
                if (!@symlink($sourdir,$destdir)) {
                    throw new Exception('Невозможно создать символическую ссылку директории ('.$sourdir.' => '.$destdir.')!');
                }
   		    }

   		    foreach ($this->filesList as $destfile => $sourfile) {
   		        $sourfile = realpath($sourfile);
  		        $this->copyLog[] = $sourfile.' => '.$destfile;
   		    	if (!@symlink($sourfile,$destfile)) {
                    throw new Exception('Невозможно создать символическую ссылку файла ('.$sourfile.' => '.$destfile.')!');
                }
   		    }

   		}

    }