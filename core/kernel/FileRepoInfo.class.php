<?php
/**
 * Содержит класс FileRepoInfo
 *
 * @package energine
 * @subpackage kernel
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 */

/**
 * Альтернативный FileInfo
 *
 * @package energine
 * @subpackage kernel
 * @author d.pavka@gmail.com
 */
class FileRepoInfo extends Object {
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
    public function __construct($fileName) {
        $this->finfo = new finfo(FILEINFO_MIME_TYPE);
        if (!$this->finfo || !file_exists($fileName)) {
            throw new SystemException('ERR_FINFO_DB', SystemException::ERR_CRITICAL);
        }
        $this->file = $fileName;
    }

    /**
     * Возвращает информацию о типе файла
     *
     * @param string имя файла
     * @return string
     * @access public
     */
    public function analyze() {
        $result['type'] = self::META_TYPE_UNKNOWN;
        $result['mime'] = 'unknown/mime-type';
        $result['width'] = null;
        $result['height'] = null;

        $filename = $this->file;

        try {
            if (is_dir($filename)) {

                $result['type'] = self::META_TYPE_FOLDER;

                $result['mime'] = 'unknown/mime-type';
            }
            elseif (!file_exists($filename)) {
                $result['mime'] = 'unknown/mime-type';
                $result['type'] =
                        self::META_TYPE_UNKNOWN;
            }
            else {
                switch (
                    $result['mime'] = $this->finfo->file($filename)) {
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
                        $result['type'] =
                                self::META_TYPE_UNKNOWN;
                        break;
                }
            }
        }
        catch (Exception $e) {

        }
        return (object)$result;
    }
}
