<?php
/**
 * Содержит класс JSONUploadBuilder
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @copyright Energine 2006
 * @version $Id$
 */


/**
 * Класс для построения JSON ответа
 * Используется для FileLibrary
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 */
class JSONUploadBuilder extends JSONBuilder {

    /**
     * Текущая директория
     *
     * @var string
     * @access private
     */
    private $currentDirectory = false;

    /**
     * Конструктор класса
     *
     * @return void
     */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * В ответ добавляется значение текущей директории
	 *
	 * @return string
	 * @access public
	 */

	public function getResult() {
	    $this->result['currentDirectory'] = $this->getCurrentDirectory();
	    $result = parent::getResult();
	    return $result;
	}

	/**
	 * Возвращает текущую директорию
	 *
	 * @return string
	 * @access public
	 */

	public function getCurrentDirectory() {
	    if (!$this->currentDirectory) {
	    	throw new SystemException('ERR_DEV_NO_CURR_DIR', SystemException::ERR_DEVELOPER);
	    }

        return $this->currentDirectory;
	}

	/**
	 * Устанавливает текущую директорию
	 *
	 * @return void
	 * @access public
	 */

	public function setCurrentDir($path) {
        $this->currentDirectory = $path;
	}
}