<?php
    /**
     * Содержит класс Linker
     *
     * @package energine
     * @subpackage configurator
     * @author Tigrenok
     * @copyright Energine 2007

     */

    require_once('Model.class.php');

    /**
     * Создает копии (или симлинки) файлов
     *
     * @package energine
     * @subpackage configurator
     */
    class Flusher extends Model {
        
        /**
         * Конструктор класса
         *
         * @return void
         */
    	public function __construct() {
    	    $this->memcache = new Memcached();
            $this->memcache->addServer('localhost', '11211');
    	}

        /**
         * Запускает модель
         *
         * @return void
         * @access public
         */
        public function run() {

            $this->getViewer()->addBlock('',Viewer::TPL_LINKER_SCRIPT);

            $this->getViewer()->addBlock('Флашер:',Viewer::TPL_HEADER);
            $this->getViewer()->addBlock('Содержимое: <p>'.var_export($this->memcache->get('structure'), true).'</p>', Viewer::TPL_DEFAULT);
            $result = $this->memcache->flush();
            
    		$this->getViewer()->addBlock('Результат очистки:'.var_export($result, true), Viewer::TPL_FOOTER);
    		$base = str_replace(array('setup/?state=install', 'setup/index.php?state=install', 'setup/?state=linker', 'setup/index.php?state=linker', 'setup/?state=memcacheflusher', 'setup/index.php?state=memcacheflusher'),'',$_SERVER['REQUEST_URI']);
    		$this->getViewer()->addBlock('<a href="'.$base.'">Перейти на сайт</a>', Viewer::TPL_FOOTER);
        }


    }
