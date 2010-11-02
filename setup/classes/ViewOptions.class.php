<?php
/**
 * Содержит класс OptionViewer
 *
 * @package energine
 * @subpackage configurator
 * @author Tigrenok
 * @copyright Energine 2007

 */

require_once('Model.class.php');

/**
 * Создает вывод вариантов работы Инсталлера
 *
 * @package energine
 * @subpackage configurator
 */
class ViewOptions extends Model {
	/**
	 * Список возможных режимов работы скрипта
	 *
	 * @var array
	 * @access private
	 */
	private $optionsSet;
	
    public function __construct(){
    	
    	$this->optionsSet = array(
	        'Проверка сервера' => array('Проверяет сервер на возможность установки системы Energine','?state=checkserver'),
	        'Полная инсталляция' => array('Полный процесс установки системы Energine','?state=install'),
        );
        
        if(DataChecker::isEnergineInstalled()){
            $this->optionsSet = array_merge(
                $this->optionsSet,
                array(
                    'Восстановление базы данных' => array('Восстановление базы данных из резервной копии','?state=sqlrestore'),
                    'Создать дамп базы' => array('Создание резервной копии базы данных','?state=sqldump'),
                    'Линкер' => array('Создание симлинков для сбора информации из модулей','?state=linker'),
                    'Очистка кеша' => array('Очистка MemCache ','?state=memcacheflusher'),
                )
            );	
        }        
    }
    
	/**
	 * Конструктор класса
	 *
	 * @return void
	 * @access public
	 */
	public function run() {
		$this->getViewer()->addBlock($this->optionsSet,Viewer::TPL_VIEWOPTS);
	}

}