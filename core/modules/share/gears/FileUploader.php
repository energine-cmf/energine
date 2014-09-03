<?php
/**
 * @file
 * FileUploader.
 *
 * It contains the definition to:
 * @code
class FileUploader;
@endcode
 *
 * @author 1m.dm
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
use Energine\share\components\FileRepository;
/**
 * File uploader for the server.
 *
 * @code
class FileUploader;
@endcode
 */
class FileUploader extends Object {
    /**
     * Description of uploaded file.
     * $_FILES
     *
     * @var array $file
     *
     * @see PHP manual, POST method uploads
     */
    protected $file = array();

    /**
     * Restriction for uploaded file.
     * @var array $restrictions
     */
    private $restrictions = array();

    /**
     * File extension.
     * @var string $ext
     */
    private $ext;

    /**
     * Filename on the server side.
     * @var string $FileObjectName
     */
    protected $FileObjectName;

    //todo VZ: Have I this right described?
    /**
     * Filename on the client side.
     * @var string $FileRealName
     */
    protected $FileRealName;

    /**
     * Root path of uploaded files.
     * @var string $uploadsPath
     */
    private $uploadsPath = '';

    /**
     * Validation flag.
     * It indicates whether the validation was done (this can be checked with @link FileUploader::upload upload @endlink);
     * @var boolean $validated
     */
    private $validated = false;

    /**
     * @param array $restrictions File restrictions.
     */
    public function __construct(Array $restrictions = array()) {
        $this->restrictions = $restrictions;
    }

    //todo VZ: For what is that array example?
    /**
     * Set uploaded file restrictions.
     *
     * @code
array(
    'ext' => array('jpg', 'gif')
)
@endcode
     *
     * @param array $restrictions Restriction
     */
    public function setRestrictions(array $restrictions) {
        $this->restrictions = $restrictions;
    }

    /**
     * Set file description.
     *
     * @param array $file File.
     *
     * @throws SystemException 'ERR_DEV_BAD_DATA'
     * @throws SystemException 'ERR_NO_FILE'
     * @throws SystemException 'ERR_UPLOAD_FAILED'
     */
    public function setFile(array $file) {
        if (!isset($file['name'], $file['size'], $file['tmp_name'], $file['error'])) {
            throw new SystemException('ERR_DEV_BAD_DATA', SystemException::ERR_DEVELOPER, $file);
        }
        
        if ($file['error'] == UPLOAD_ERR_NO_FILE){
            throw new SystemException('ERR_NO_FILE', SystemException::ERR_NOTICE, $file);                    
        }
        
        if($file['error'] != UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            throw new SystemException('ERR_UPLOAD_FAILED', SystemException::ERR_WARNING, $file['error']);
        }
        $this->file = $file;
        
        $this->validate();
    }

    /**
     * Validate uploaded file.
     *
     * @return boolean
     *
     * @throws SystemException 'ERR_DEV_BAD_DATA'
     * @throws SystemException 'ERR_BAD_FILE_TYPE'
     */
    public function validate() {
        // Браузер может не посылать MIME type, поэтому расчитывать на него нельзя.
        if (empty($this->file)) {
            throw new SystemException('ERR_DEV_BAD_DATA', SystemException::ERR_DEVELOPER, $this->file['name']);
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

    //todo VZ: Why only true is returned?
    //todo VZ: The description for input argument is not very clear.
    /**
     * Upload file into the directory.
     *
     * @param string $dir Directory within the root directory of the downloaded files. (директория внутри корневого каталога загружаемых файлов)
     * @return boolean
     *
     * @throws SystemException 'ERR_DEV_UPLOAD_FAILED'
     */
    public function upload($dir) {
        if (!$this->validated) {
            $this->validate();
        }
        
        $filePath = $this->generateFilename($dir, $this->ext);
        if (
            !@move_uploaded_file(
                $this->file['tmp_name'], 
                $filePath
            )
        ) {
            throw new SystemException('ERR_DEV_UPLOAD_FAILED', SystemException::ERR_WARNING, $this->file['name']);
        }
        //@todo Отключено до выяснения
        //Ресайзим изображение
        /*
        if(in_array($this->getExtension(), array('gif', 'png', 'jpg', 'jpeg'))){
//            $img = new Image();
//            $img->loadFromFile($filePath);
//
//            if(($img->getWidth()> 800) && ($img->getHeight() > 600)){
//	            $img->resize(800, 600);
//	            $img->saveToFile($filePath);
//            }
//            unset($image);
        	// ------------------------
//            elseif($img->getWidth()> 800){
//            	$img->resize(800, null);
//                $img->saveToFile($filePath);
//            }
//            elseif($img->getHeight()> 600){
//                $img->resize(null, 600);
//                $img->saveToFile($filePath);
//            }
        }
        */
        
        $this->FileRealName = $this->file['name'];
        $this->FileObjectName = $filePath;
        chmod($this->FileObjectName, 0666);

        return true;
    }

    /**
     * Generate filename.
     *
     * @param string $dir Directory.
     * @param string $ext File extension.
     * @return string
     */
    protected function generateFilename($dir, $ext){
        if ($dir[0] == '/') {
            $dir = substr($dir, 1);
        }
        if ($dir[strlen($dir)-1] != '/') {
            $dir .= '/';
        }

        return $this->uploadsPath.$dir.FileRepository::generateFilename($this->uploadsPath.$dir, $ext);
    }

    /**
     * Get the uploaded filename on the server side.
     *
     * @return string
     */
    public function getFileObjectName() {
        return $this->FileObjectName;
    }

    //todo VZ: Have I this right described?
    /**
     * Get the filename on the client side.
     *
     * @return string
     */
    public function getFileRealName() {
        return $this->FileRealName;
    }

    /**
     * Get the file extension.
     *
     * @return string
     */
    public function getExtension() {
        return $this->ext;
    }

    /**
     * Clean the object properties for further reuse.
     */
    public function cleanUp() {
        $this->restrictions = array();
        $this->ext = null;
        $this->FileObjectName = null;
        $this->validated = false;
    }
}
