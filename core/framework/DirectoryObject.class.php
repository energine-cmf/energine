<?php

/**
 * Содержит класс Uploads
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @copyright Energine 2006
 * @version $Id$
 */

//require_once('core/framework/FileSystemObject.class.php');
//require_once('core/framework/FileObject.class.php');

/**
 * Класс предназначенный для обработки набора FileObject
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 */

class DirectoryObject extends FileSystemObject {
    /**
     * Directory handle
     *
     * @var resource
     * @access private
     */
    private $dirHandle = null;

    /**
     * Массив файлов
     *
     * @var array
     * @access private
     */
    private $files = array();

    /**
     * Текущий ключ
     *
     * @var int
     * @access private
     */
    private $iterator = 0;

    /**
     * Конструктор класса
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
	 * Статический метод загрузки возвращающий self
	 *
	 * @param string путь к файлу
	 * @return DirectoryObject
	 * @access public
	 * @static
	 */

    public static function loadFrom($path) {
        if (!file_exists($path) || !is_dir($path)) {
            throw new SystemException('ERR_DEV_NO_FILE', SystemException::ERR_DEVELOPER, $path);
        }
        if (!is_writeable($path)) {
            throw new SystemException('ERR_DEV_DIR_NOT_WRITABLE', SystemException::ERR_DEVELOPER, $path);
        }
        $result = new DirectoryObject();
        $result->load($path);
        return $result;
    }

    /**
	 * Деструктор закрывает открытый ресурс
	 *
	 * @return void
	 * @access public
	 */

    public function __destruct() {
        if (!empty($this->dirHandle)) {
        	closedir($this->dirHandle);
        }
    }

    /**
     * Открывает директорию
     *
     * @return void
     * @access public
     */

    public function open() {
        if (!($this->dirHandle = opendir($this->getPath()))) {
            throw new SystemException('ERR_DEV_UNABLE_OPEN_DIR', SystemException::ERR_DEVELOPER, $this->path);
        }
        while (false !== ($fileName = readdir($this->dirHandle))) {
            if (substr($fileName, 0, 1) != '.') {
                $fullPath = $this->getPath().'/'.$fileName;

                if (isset($_POST['onlymedia']) && !in_array(FileInfo::getInstance()->analyze($fullPath)->type, array(FileInfo::META_TYPE_IMAGE, FileInfo::META_TYPE_FOLDER, FileInfo::META_TYPE_VIDEO))) {
                    //dummy
                }
                else {
                    $this->files[] = (is_dir($fullPath))?DirectoryObject::loadFrom($fullPath):FileObject::loadFrom($fullPath);
                    //inspect($this->files);
                }
            }
        }
    }

    /**
     * Создание папки
     *
     * @param array данные папки
     * @return boolean
     * @access public
     */

    public function create($data) {
        $result = false;
        if (!isset($data[self::TABLE_NAME])) {
        	throw new SystemException('ERR_DEV_INSUFFICIENT_DATA', SystemException::ERR_DEVELOPER);
        }

        if(!isset($data[self::TABLE_NAME]['upl_path'])){
			$data[self::TABLE_NAME]['upl_path'] = Translit::transliterate($data[self::TABLE_NAME]['upl_name'], '_', true);
		}
        $data[self::TABLE_NAME]['upl_path'] = $data['path'].'/'.$data[self::TABLE_NAME]['upl_path'];
        unset($data['path']);
        $data = $data[self::TABLE_NAME];

        if (!file_exists($data['upl_path'])) {
            $result = @mkdir($data['upl_path']);
        }

        if ($result) {
            $this->dbh->modify(QAL::INSERT, self::TABLE_NAME, $data);
        }
        return $result;
    }

    /**
     * Удаление папки
     *
     * @return boolean
     * @access public
     */

    public function delete() {
        if (@rmdir($this->getPath())) {
            parent::delete();
        }
    }

    /**
     * Переходит к следующему объекту
     *
     * @return type
     * @access public
     */

    public function next() {
        $this->iterator++;
    }

    /**
     * Перемещается на первый єлемент
     *
     * @return void
     * @access public
     */

    public function rewind() {
        $this->iterator = 0;
    }

    /**
     * Возвращает текущий объект
     *
     * @return mixed
     * @access public
     */

    public function current() {
        return $this->files[$this->iterator];
    }

    /**
     * Возворащает ключ текущего объекта
     *
     * @return int
     * @access public
     */

    public function key() {
        return $this->iterator;
    }

    /**
     * Проверяет существует ли текущий елемент
     *
     * @return boolean
     * @access public
     */

    public function valid() {
        return !empty($this->dirHandle) && $this->iterator<sizeof($this->files);
    }

    /**
     * Возвращает объект в виде массива
     * Если он не открыт, возвращается информация о самом объексте - иначе, о всех вложенных объектах
     *
     * @return array
     * @access public
     */

    public function asArray() {
        $result = array();
        if (empty($this->dirHandle)) {
            $result = parent::asArray();
        }
        else {
        	foreach ($this->files as $file) {
        	    $data = $file->asArray();
        		$result[] = $data;
        	}
        	usort($result, array($this, 'sortFileNames'));
        }
        return $result;
    }

    /**
     * Сортировка содержимого папки по алфавиту
     *
     * @return int
     * @access private
     */

    private function sortFileNames($current, $next) {
        return strcasecmp($current['upl_name'], $next['upl_name']);
    }
}

