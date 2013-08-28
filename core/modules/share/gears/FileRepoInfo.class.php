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
class FileRepoInfo extends DBWorker {
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
        parent::__construct();
        $this->finfo = new finfo(FILEINFO_MIME_TYPE);
        $this->getFInfoSQL = $this->dbh->getPDO()->prepare('SELECT upl_internal_type as type, upl_mime_type as mime, upl_width as width, upl_height as height FROM share_uploads WHERE upl_path = ?');
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
        $result['width'] = '';
        $result['height'] = '';

        // hotfix для php на продакшне без поддержки https://
        // todo: пофиксить, до выяснения
        if (strpos($filename, 'https://') !== false) {
            $result['mime'] = 'unknown/mime-type';
            $result['type'] = self::META_TYPE_UNKNOWN;
            return $result;
        } elseif (is_dir($filename)) {
            $result['type'] = self::META_TYPE_FOLDER;
            $result['mime'] = 'unknown/mime-type';
        } elseif (!file_exists($filename)) {
            $result['mime'] = 'unknown/mime-type';
            $result['type'] = self::META_TYPE_UNKNOWN;
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

    /**
     * Возвращает объект IFileRepository для обработки видео-файлов в репозитарии
     *
     * @param int $upl_id
     * @return IFileRepository|FileRepositoryLocal|FileRepositoryRO
     * @throws SystemException
     */
    public function getRepositoryInstanceById($upl_id) {
        $upl_path = $this->dbh->getScalar('share_uploads', 'upl_path', array('upl_id' => $upl_id));
        $upl_root = $this->getRepositoryRoot($upl_path);
        return $this->getRepositoryInstanceByRepoPath($upl_root);
    }

    /**
     * Возвращает объект IFileRepository для обработки видео-файлов в репозитарии
     *
     * @param string $upl_path
     * @return IFileRepository|FileRepositoryLocal|FileRepositoryRO
     * @throws SystemException
     */
    public function getRepositoryInstanceByPath($upl_path) {
        $upl_root = $this->getRepositoryRoot($upl_path);
        return $this->getRepositoryInstanceByRepoPath($upl_root);
    }

    /**
     * Возвращает root репозитария (например uploads/public) из полного пути к файлу
     *
     * @param string $upl_path
     * @return string
     * @throws SystemException
     */
    public function getRepositoryRoot($upl_path) {
        $upl_junks = explode('/', $upl_path, 3);
        if (empty($upl_junks[1])) {
            //throw new SystemException('ERR_INVALID_UPL_PATH');
            $upl_root = '';
        }
        else {
            $upl_root = $upl_junks[0] . '/' . $upl_junks[1];
        }

        return $upl_root;
    }

    /**
     * Возвращает инстанс IFileRepository по идентификатору $repo_id
     *
     * @param string $upl_root
     * @return FileRepositoryLocal|IFileRepository
     */
    protected function getRepositoryInstanceByRepoPath($upl_root) {

        $repo_id = $this->dbh->getScalar('share_uploads', 'upl_id', array('upl_path' => $upl_root));

        $cfg = E()->getConfigValue('repositories.mapping');

        if ($cfg) {

            $repo_mime = $this->dbh->getScalar('share_uploads', 'upl_mime_type', array('upl_id' => $repo_id));

            if (!empty($cfg[$repo_mime])) {
                $repo_class_name = $cfg[$repo_mime];
                $result = new $repo_class_name($repo_id, $upl_root);
                if ($result instanceof IFileRepository) {
                    return $result;
                }
            }
        }

        // fallback
        $result = new FileRepositoryLocal($repo_id, 'uploads/public');
        return $result;
    }
}
