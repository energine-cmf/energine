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
     * @var PDOStatement
     */
    private $getFInfoSQL;

    /**
     * Конструктор класса
     *
     * @access public
     */
    public function __construct() {
        $this->finfo = new finfo(FILEINFO_MIME_TYPE);
        $this->getFInfoSQL = E()->getDB()->getPDO()->prepare('SELECT upl_internal_type as type, upl_mime_type as mime, upl_width as width, upl_height as height FROM share_uploads WHERE upl_path = ?');
    }

    /**
     * @param $filename
     * @param bool $forceReadFromFile
     * @return mixed|object
     * @throws Exception
     */
    public function analyze($filename, $forceReadFromFile = false) {
        try {
            if (
                $forceReadFromFile
                ||
                !$this->getFInfoSQL->execute(array($filename))
                ||
                !($result = $this->getFInfoSQL->fetch(PDO::FETCH_ASSOC))
            ) {
                if(!($result = $this->getFileInfoData($filename)))
                    throw new Exception();
            }
        } catch (Exception $e) {
            $result['type'] = self::META_TYPE_UNKNOWN;
            $result['mime'] = 'unknown/mime-type';
            $result['width'] = null;
            $result['height'] = null;
        }

        return (object)$result;
    }

    /**
     * @param $filename
     * @return mixed
     */
    private function getFileInfoData($filename) {
        if (is_dir($filename)) {
            $result['type'] = self::META_TYPE_FOLDER;

            $result['mime'] = 'unknown/mime-type';
        } elseif (!file_exists($filename)) {
            $result['mime'] = 'unknown/mime-type';
            $result['type'] =
                self::META_TYPE_UNKNOWN;
        } else {
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
                case 'video/mp4':
                    $result['type'] = self::META_TYPE_VIDEO;
                    break;
                case 'text/csv':
                case 'text/plain':
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

        return $result;
    }
}
