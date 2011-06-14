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
class FileInfo extends DBWorker {
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
        $data = array();
        $data['upl_internal_type'] = $result['type'] = self::META_TYPE_UNKNOWN;
        $data['upl_mime_type'] = $result['mime'] = 'unknown/mime-type';
        $mc = E()->getCache();
        if($mc->isEnabled()){
            if(!($fileInfo = $mc->retrieve('info.'.$filename))){
                $fileInfo = $this->dbh->select('share_uploads', array('upl_internal_type as type', 'upl_mime_type as mime', 'upl_width as width', 'upl_height as height'), array('upl_path' => $filename));
                $mc->store('info.'.$filename, $fileInfo);
            }
        }
        else {
            $fileInfo =
                    $this->dbh->select('share_uploads', array('upl_internal_type as type', 'upl_mime_type as mime', 'upl_width as width', 'upl_height as height'), array('upl_path' => $filename));
        }

        if (is_array($fileInfo) && !empty($fileInfo) && $fileInfo[0]['type']) {
            list($fileInfo) = $fileInfo;
                //Если есть информация в этом поле значит уже обращались к нему
                $result = array_filter($fileInfo);
        }
        else {
            try {
                if (is_dir($filename)) {
                    $data['upl_internal_type'] =
                            $result['type'] = self::META_TYPE_FOLDER;
                    $data['upl_mime_type'] =
                            $result['mime'] = 'unknown/mime-type';
                }
                elseif(!file_exists($filename)){
                    $result['mime'] = 'unknown/mime-type';
                    $result['type'] =
                                    self::META_TYPE_UNKNOWN;
                }
                else {
                    switch (
                    $data['upl_mime_type'] = $result['mime'] =
                            $this->getMimeType($filename)) {
                        case 'image/jpeg':
                        case 'image/png':
                        case 'image/gif':
                            $tmp = getimagesize($filename);
                            $data['upl_internal_type'] =
                                    $result['type'] = self::META_TYPE_IMAGE;
                            $data['upl_width'] = $result['width'] = $tmp[0];
                            $data['upl_height'] =
                                    $result['height'] = $tmp[1];
                            break;
                        case 'video/x-flv':
                            $data['upl_internal_type'] =
                                    $result['type'] = self::META_TYPE_VIDEO;
                            break;
                        case 'text/csv':
                            $data['upl_internal_type'] =
                                    $result['type'] = self::META_TYPE_TEXT;
                            break;
                        case 'application/zip':
                            $data['upl_internal_type'] =
                                    $result['type'] = self::META_TYPE_ZIP;
                            break;
                        default:
                            $data['upl_internal_type'] = $result['type'] =
                                    self::META_TYPE_UNKNOWN;
                            break;
                    }
                    //stop($data);
                    $this->dbh->modify(QAL::UPDATE, 'share_uploads', $data, array('upl_path' => $filename));
                    if($mc->isEnabled()){
                        $mc->store('info.'.$filename, array($result));
                    }
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
