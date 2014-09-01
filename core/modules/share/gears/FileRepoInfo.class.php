<?php
/**
 * @file
 * FileRepoInfo
 *
 * It contains the definition to:
 * @code
class FileRepoInfo;
@endcode
 *
 * @author d.pavka
 * @copyright d.pavka@gmail.com
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * Alternative to FileInfo.
 *
 * @code
class FileRepoInfo;
@endcode
 */
class FileRepoInfo extends DBWorker {
    /**
     * Image meta type.
     * @var string META_TYPE_IMAGE
     */
    const META_TYPE_IMAGE = 'image';
    /**
     * Video meta type.
     * @var string META_TYPE_VIDEO
     */
    const META_TYPE_VIDEO = 'video';
    /**
     * Audio meta type.
     * @var string META_TYPE_AUDIO
     */
    const META_TYPE_AUDIO = 'audio';
    /**
     * Zip meta type.
     * @var string META_TYPE_ZIP
     */
    const META_TYPE_ZIP = 'zip';
    /**
     * Text meta type.
     * @var string META_TYPE_TEXT
     */
    const META_TYPE_TEXT = 'text';
    /**
     * Folder meta type.
     * @var string META_TYPE_FOLDER
     */
    const META_TYPE_FOLDER = 'folder';
    /**
     * Unknown meta type.
     * @var string META_TYPE_UNKNOWN
     */
    const META_TYPE_UNKNOWN = 'unknown';

    /**
     * File info object.
     * @var \finfo $finfo
     */
    private $finfo;
    /**
     * @var \PDOStatement $getFInfoSQL
     */
    private $getFInfoSQL;

    public function __construct() {
        parent::__construct();
        $this->finfo = new \finfo(FILEINFO_MIME_TYPE);
        $this->getFInfoSQL = $this->dbh->getPDO()->prepare('SELECT upl_internal_type as type, upl_mime_type as mime, upl_width as width, upl_height as height, upl_is_mp4 as is_mp4, upl_is_webm as is_webm, upl_is_flv as is_flv FROM share_uploads WHERE upl_path = ?');
    }

    /**
     * Analyze file.
     *
     * @param string $filename File name.
     * @param bool $forceReadFromFile
     * @return mixed|object
     *
     * @throws \Exception
     */
    public function analyze($filename, $forceReadFromFile = false) {
        try {
            if ($forceReadFromFile
                || !$this->getFInfoSQL->execute(array($filename))
                || !($result = $this->getFInfoSQL->fetch(\PDO::FETCH_ASSOC))
            ) {
                if(!($result = $this->getFileInfoData($filename))){
                    throw new \Exception();

                }

            }
        } catch (\Exception $e) {
            $result['type'] = self::META_TYPE_UNKNOWN;
            $result['mime'] = 'unknown/mime-type';
            $result['width'] = null;
            $result['height'] = null;
            $result['is_flv'] = false;
            $result['is_webm'] = false;
            $result['is_mp4'] = false;
        }

        return (object)$result;
    }

    /**
     * Get file information data.
     *
     * @param string $filename Filename.
     * @return mixed
     */
    private function getFileInfoData($filename) {
        $result['width'] = '';
        $result['height'] = '';
        $result['is_mp4'] = false;
        $result['is_webm'] = false;
        $result['is_flv'] = false;

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

    //todo VZ: where it throws?
    /**
     * Get repository instance by ID.
     * It returns IFileRepository for processing video files into the repository.
     *
     * @param int $upl_id Uploads ID.
     * @return IFileRepository|FileRepositoryLocal|FileRepositoryRO
     *
     * @throws SystemException
     */
    public function getRepositoryInstanceById($upl_id) {
        $upl_path = $this->dbh->getScalar('share_uploads', 'upl_path', array('upl_id' => $upl_id));
        $upl_root = $this->getRepositoryRoot($upl_path);
        return $this->getRepositoryInstanceByRepoPath($upl_root);
    }

    //todo VZ: where it throws?
    /**
     * Get repository instance by path.
     * It returns IFileRepository for processing video files into the repository.
     *
     * @param string $upl_path Uploads path.
     * @return IFileRepository|FileRepositoryLocal|FileRepositoryRO
     *
     * @throws SystemException
     */
    public function getRepositoryInstanceByPath($upl_path) {
        $upl_root = $this->getRepositoryRoot($upl_path);
        return $this->getRepositoryInstanceByRepoPath($upl_root);
    }

    //todo VZ: where it throws?
    /**
     * Get repository root path.
     * It returns repository @c root from the whole file path. For example <tt>uploads/public</tt>
     *
     * @param string $upl_path Uploads path.
     * @return string
     *
     * @throws SystemException
     */
    public function getRepositoryRoot($upl_path) {
        $upl_junks = explode('/', $upl_path, 3);
        if (empty($upl_junks[1])) {
            //todo VZ: remove this?
            //throw new SystemException('ERR_INVALID_UPL_PATH');
            $upl_root = '';
        }
        else {
            $upl_root = $upl_junks[0] . '/' . $upl_junks[1];
        }

        return $upl_root;
    }

    /**
     * Get repository instance by repository path.
     * It returns the instance of IFileRepository by @c $repo_id.
     *
     * @param string $upl_root Uploads root path.
     * @return FileRepositoryLocal|IFileRepository
     */
    protected function getRepositoryInstanceByRepoPath($upl_root) {

        $repo_id = $this->dbh->getScalar('share_uploads', 'upl_id', array('upl_path' => $upl_root));

        $cfg = E()->getConfigValue('repositories.mapping');

        if ($cfg) {

            $repo_mime = $this->dbh->getScalar('share_uploads', 'upl_mime_type', array('upl_id' => $repo_id));

            if (!empty($cfg[$repo_mime])) {
                $repo_class_name = $cfg[$repo_mime];
                $repo_class_name = 'Energine\\share\\gears\\'.$repo_class_name;
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
