<?php

/**
 * Класс FileUploader.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 * @copyright ColoCall 2006
 * @version $Id$
 */

//require_once('core/framework/Object.class.php');

/**
 * Загрузчик файлов на сервер.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 */
class FileUploader extends Object {

    /**
     * @access private
     * @var array описание загружаемого файла
     * @see PHP manual, POST method uploads
     */
    private $file = array();

    /**
     * @access private
     * @var array ограничения для загружаемого файла
     */
    private $restrictions = array();

    /**
     * @access private
     * @var string расширение файла
     */
    private $ext;

    /**
     * @access private
     * @var string имя, под которым загруженный файл сохранен на сервере
     */
    private $FileObjectName;

    /**
     * @access private
     * @var string путь к корневому каталогу загружаемых файлов
     */
    private $uploadsPath = '';

    /**
     * @access private
     * @var boolean флаг, указывающий была ли произведена валидация (проверяется методом upload)
     */
    private $validated = false;

    ////////////////////////////////////////////////////////////////////////////

    /**
     * Конструктор класса.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        parent::__construct();
        $this->restrictions = array(
            'width' => false,
            'height' => false,
            'precision' => false,
            'max' => false
        );
    }

    /**
     * Устанавливает ограничения которым должен соответствовать загружаемый
     *
     * Для изображений:
     *     array(
     *         'width'   => integer, // ширина ищображения
     *         'height'  => integer, // высота изображения
     *         'precise' => boolean  // true - точный размер, false - не более указанного (по-умолчанию),
     *         'max' => integer //максимальный размер файла
     *     )
     *
     * @access public
     * @param array $restrictions
     * @return void
     */
    public function setRestrictions(array $restrictions) {
        $this->restrictions = $restrictions;
    }

    /**
     * Устанавливает описание файла.
     *
     * @access public
     * @param array $file
     * @return void
     */
    public function setFile(array $file) {
        if (!isset($file['name'], $file['size'], $file['tmp_name'], $file['error'])) {
            throw new SystemException('ERR_DEV_BAD_DATA', SystemException::ERR_DEVELOPER, $file);
        }
        $this->file = $file;
    }

    /**
     * Валидация загружаемого файла.
     *
     * @access public
     * @return boolean
     */
    public function validate() {
        /*
         * Браузер может не посылать MIME type, поэтому расчитывать на него нельзя.
         */
        if (empty($this->file)) {
            throw new SystemException('ERR_DEV_BAD_DATA', SystemException::ERR_DEVELOPER, $this->file);
        }

        if ($this->file['error'] != UPLOAD_ERR_OK || !is_uploaded_file($this->file['tmp_name'])) {
            throw new SystemException('ERR_UPLOAD_FAILED', SystemException::ERR_WARNING, $this->file['error']);
        }

        $this->ext = strtolower(substr(strrchr($this->file['name'], '.'), 1));
/*

        $size = @getimagesize($this->file['tmp_name']);
        if (!$size) {
            throw new SystemException('ERR_INVALID_IMAGE', SystemException::ERR_WARNING, $this->file['tmp_name']);
        }

        if (isset($this->restrictions['width']) &&
            (($this->restrictions['precise'] && $size[0] != $this->restrictions['width']) ||
            $size[0] > $this->restrictions['width'])) {
            throw new SystemException('ERR_BAD_IMAGE_WIDTH', SystemException::ERR_WARNING, $size[0]);
        }

        if (isset($this->restrictions['height']) &&
            (($this->restrictions['precise'] && $size[1] != $this->restrictions['height']) ||
            $size[1] > $this->restrictions['height'])) {
            throw new SystemException('ERR_BAD_IMAGE_HEIGHT', SystemException::ERR_WARNING, $size[1]);
        }


        if ($this->file['size'] > $this->maxsize) {
            throw new SystemException('ERR_UPLOADED_FILE_EXCEEDS_SIZE_LIMIT', SystemException::ERR_WARNING, $this->file['size']);
        }
*/
        return ($this->validated = true);
    }

    /**
     * Фактическая загрузка файла в определенную директорию.
     *
     * @access public
     * @param string $dir директория внутри корневого каталога загружаемых файлов
     * @return boolean
     */
    public function upload($dir) {
        if (!$this->validated) {
            $this->validate();
        }

        if ($dir[0] == '/') {
            $dir = substr($dir, 1);
        }
        if ($dir[strlen($dir)-1] != '/') {
            $dir .= '/';
        }

        /*
         * Генерируем уникальное имя файла.
         */
        $c = ''; // первый вариант имени не будет включать символ '0'
        do {
            $filename = time()."$c.{$this->ext}";
            $c++; // при первом проходе цикла $c приводится к integer(1)
        } while(file_exists($this->uploadsPath.$dir.$filename));

        if (!@move_uploaded_file($this->file['tmp_name'], $this->uploadsPath.$dir.$filename)) {
            throw new SystemException('ERR_DEV_UPLOAD_FAILED', SystemException::ERR_WARNING, $this->file);
        }
        $this->FileObjectName = $this->uploadsPath.$dir.$filename;
        chmod($this->FileObjectName, 0644);

        return true;
    }

    /**
     * Возвращает имя загруженного файла.
     *
     * @access public
     * @return string
     */
    public function getFileObjectName() {
        return $this->FileObjectName;
    }

    /**
     * Возвращает расширение файла.
     *
     * @access public
     * @return string
     */
    public function getExtension() {
        return $this->ext;
    }

    /**
     * Очищает состояние объекта для повторного использования.
     *
     * @access public
     * @return void
     */
    public function cleanUp() {
        $this->restrictions = array();
        $this->ext = null;
        $this->FileObjectName = null;
        $this->validated = false;
    }
}
