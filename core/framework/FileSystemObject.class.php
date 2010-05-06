<?php

/**
 * Содержит класс FileSystemObject
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @copyright Energine 2006
 * @version $Id$
 */


/**
 * Абстрактный класс  - модель объекта файловой системы
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @abstract
 */
abstract class FileSystemObject extends DBWorker {
    /**
     * Имя таблицы в которой хранится мета описания папки
     *
     */
    const TABLE_NAME = 'share_uploads';
    /**
     * Полный путь к файлу
     *
     * @var string
     * @access private
     */
    private $path;
    /**
     * Имя папки
     * Если существует описание в БД - берется из нее, если нет. то из $this->path
     *
     * @var string
     * @access private
     */
    private $name;

    /**
     * Данные присоединенные к файлу
     *
     * @var mixed
     * @access private
     */
    private $data = null;

    /**
     * Идентификатор записи, хранящей данные о папке в БД
     * Может быть  пустым
     *
     * @var mixed
     * @access private
     */
    private $id = false;

    /**
     * Конструктор класса
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Возвращает объект в виде массива
     * Если он не открыт, возвращается информация о самом объекте - иначе, о всех вложенных объектах
     *
     * @return array
     * @access public
     */

    public function asArray() {
        $result = array(
        'upl_id'=>$this->id,
        'upl_mime_type' => FileInfo::getInstance()->analyze($this->path)->type,
        'upl_name' => $this->name,
        'upl_path' => $this->path,
        'upl_data'=>$this->data
        );
        return $result;
    }

    /**
     * Загружает мета описание данных о объекте из БД
     *
     * @return void
     * @access protected
     */

    protected function loadData($path) {
        $this->path = $path;
        $this->name = basename($this->path);
        $res = $this->dbh->select(self::TABLE_NAME, true, array('upl_path'=>$this->path));
        if (is_array($res)) {
            list($res) = $res;
            $this->id = $res['upl_id'];
            $this->name = $res['upl_name'];
            $this->data = ($res['upl_data'])?unserialize($res['upl_data']):null;
        }
    }

    /**
     * Возвращает данные присоединенные к файлу
     *
     * @return mixed
     * @access public
     */

    public function getData() {
        return $this->data;
    }
    /**
     * Устанавливает данные
     *
     * @return void
     * @access public
     */

    public function setData($data) {
        $this->data = $data;
    }

    /**
     * Возвращает путь
     *
     * @return string
     * @access public
     */

    public function getPath() {
        return $this->path;
    }
    /**
     * Удаление из БД записи о файле
     *
     * @return void
     * @access public
     */

    public function delete() {
        $this->dbh->modify(QAL::DELETE, self::TABLE_NAME, null, array('upl_path'=>$this->getPath()));
    }

    /**
     * Переименование файла/папки
     *
     * @return bool
     * @access public
     */

    public function rename($name) {
        if($this->dbh->select(self::TABLE_NAME, array('upl_name'), array('upl_path'=>$this->getPath())) === true) {
            $this->dbh->modify(QAL::INSERT, self::TABLE_NAME, array('upl_name'=>$name,'upl_path'=>$this->getPath()));
        }
        else {
            $this->dbh->modify(QAL::UPDATE, self::TABLE_NAME, array('upl_name'=>$name), array('upl_path'=>$this->getPath()));
        }

    }
}