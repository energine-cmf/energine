<?php

/**
 * Класс FileUploader.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 * @copyright Energine 2006
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
     * @access protected
     * @var array описание загружаемого файла - $_FILE
     * @see PHP manual, POST method uploads
     */
    protected $file = array();

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
     * @access protected
     * @var string имя, под которым загруженный файл сохранен на сервере
     */
    protected $FileObjectName;

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
    public function __construct(Array $restrictions = array()) {
        parent::__construct();
        $this->restrictions = $restrictions;
    }

    /**
     * Устанавливает ограничения которым должен соответствовать загружаемый
     *
     * 
     * array(
     *      'ext' => array('jpg', 'gif')
     * )
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
        
        $this->validate();
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
            throw new SystemException('ERR_DEV_BAD_DATA', SystemException::ERR_DEVELOPER, $this->file['name']);
        }

        if ($this->file['error'] != UPLOAD_ERR_OK || !is_uploaded_file($this->file['tmp_name'])) {
            throw new SystemException('ERR_UPLOAD_FAILED', SystemException::ERR_WARNING, $this->file['error']);
        }
        
        $dummy = explode('.', $this->file['name']);
        $this->ext = array_pop($dummy);
        
        if(isset($this->restrictions['ext'])){
        	if(!in_array($this->ext, $this->restrictions['ext'])){
        		throw new SystemException('ERR_BAD_FILE_TYPE', SystemException::ERR_DEVELOPER, $this->file['name']);
        	}
        }
        
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
        if (
            !@move_uploaded_file(
                $this->file['tmp_name'], 
                $filePath = $this->generateFilename($dir, $this->ext)
             )
        ) {
            throw new SystemException('ERR_DEV_UPLOAD_FAILED', SystemException::ERR_WARNING, $this->file['name']);
        }
        $this->FileObjectName = $filePath;
        chmod($this->FileObjectName, 0666);

        return true;
    }
    
    protected function generateFilename($dir, $ext){
        if ($dir[0] == '/') {
            $dir = substr($dir, 1);
        }
        if ($dir[strlen($dir)-1] != '/') {
            $dir .= '/';
        }

        return $this->uploadsPath.$dir.FileObject::generateFilename($this->uploadsPath.$dir, $ext);
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
