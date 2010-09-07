<?php 
/**
 * Содержит класс FileInfo
 *
 * @package energine
 * @subpackage core
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

/**
 * Синглтон возвращающий информацию о типе файла
 *
 * @package energine
 * @subpackage core
 * @author d.pavka@gmail.com
 */
class FileInfo extends Singleton {
    /**
     * Мета тип image
     */
    const META_TYPE_IMAGE = 'image';
    /**
     * Мета тип видео
     */
    const META_TYPE_VIDEO = 'video';
    /**
     * Мета тип audio
     */
    const META_TYPE_AUDIO = 'audio';
    /**
     * Мета тип zip
     */
    const META_TYPE_ZIP = 'zip';

    const META_TYPE_TEXT = 'text';
    /**
     * Мета тип папка
     */
    const META_TYPE_FOLDER = 'folder';

    const META_TYPE_UNKNOWN = 'unknown';

    /**
     * Finfo object
     *
     * @access private
     * @var Fileinfo
     */
    private $finfo;

    /**
     * Конструктор класса
     *
     * @access public
     */
    public function __construct() {
        parent::__construct();
        $this->finfo = new finfo(FILEINFO_MIME_TYPE);
        if (!$this->finfo) {
            throw new SystemException('ERR_FINFO_DB', SystemException::ERR_CRITICAL);
        }
    }

    /**
     * Возвращает информацию о типе файла
     *
     * @param string имя файла
     * @return string
     * @access public
     */
    public function analyze($filename) {
        /*if(!file_exists($filename)){
              $result['type'] = self::META_TYPE_UNKNOWN;
              $result['mime'] = 'unknown/mime-type';
          }
          else*/
        $result['type'] = self::META_TYPE_UNKNOWN;
        $result['mime'] = 'unknown/mime-type';

        if (is_dir($filename)) {
            $result['type'] = self::META_TYPE_FOLDER;
            $result['mime'] = 'unknown/mime-type';
        }
        else {
            try {
                switch ($result['mime'] = $this->getMimeType($filename)) {
                    case 'image/jpeg':
                    case 'image/png':
                    case 'image/gif':
                        $tmp = getimagesize($filename);
                        $result['type'] = self::META_TYPE_IMAGE;
                        $result['width'] = $tmp[0];
                        $result['height'] = $tmp[1];
                        break;
                    case 'video/x-flv':
                        $result['type'] = self::META_TYPE_VIDEO;
                        break;
                    case 'text/csv':
                        $result['type'] = self::META_TYPE_TEXT;
                        break;
                    case 'application/zip':
                        $result['type'] = self::META_TYPE_ZIP;
                        break;
                    default:
                        $result['type'] = self::META_TYPE_UNKNOWN;
                        break;
                }
            }
            catch (Exception $e) {
            }

        }
        return (object) $result;
    }

    /**
     * Возвращает mimetype
     *
     * @return string
     * @access public
     */
    public function getMimeType($filename) {
        return $this->finfo->file($filename);
    }
}
