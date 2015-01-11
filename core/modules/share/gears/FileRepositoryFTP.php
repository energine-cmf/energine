<?php
/**
 * @file
 * FileRepositoryFTP.
 *
 * It contains the definition to:
 * @code
class FileRepositoryFTP;
 * @endcode
 *
 * @author Andy Karpov <andy.karpov@gmail.com>
 * @copyright Energine 2013
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
use Energine\share\components\FileRepository;

/**
 * Implementation of file loader interface IFileRepository for remote FTP repositories.
 *
 * @code
class FileRepositoryFTP;
 * @endcode
 *
 * This is useful for the cases when the repository is remote and the file is downloaded over FTP by using admin tools.
 *
 * <b>Rights table</b>
 * <table>
 *      <tr>
 *          <td><b>Action</b></td>
 *          <td><b>Allowed?</b></td>
 *      </tr>
 *      <tr>
 *          <td>CreateDir</td>
 *          <td>true</td>
 *      </tr>
 *      <tr>
 *          <td>UploadFile</td>
 *          <td>true</td>
 *      </tr>
 *      <tr>
 *          <td>EditDir</td>
 *          <td>true</td>
 *      </tr>
 *      <tr>
 *          <td>EditFile</td>
 *          <td>true</td>
 *      </tr>
 *      <tr>
 *          <td>DeleteDir</td>
 *          <td>true</td>
 *      </tr>
 *      <tr>
 *          <td>DeleteFile</td>
 *          <td>true</td>
 *      </tr>
 * </table>
 */
class FileRepositoryFTP extends Object implements IFileRepository {
    // путь FTP начиная от FTP root для загрузки alt-файлов
    /**
     * Path to the cache for alternative images.
     * @var string IMAGE_ALT_CACHE
     */
    const IMAGE_ALT_CACHE = 'resizer/w[width]-h[height]/[upl_path]';

    /**
     * Internal repository ID.
     * @var int $id
     */
    protected $id;

    /**
     * Base path to the repository.
     * @var string $base
     */
    protected $base;

    /**
     * FTP object for file downloading.
     * @var FTP $ftp_media
     */
    protected $ftp_media;

    /**
     * FTP object for loading @c alts.
     * @var FTP $ftp_alts
     */
    protected $ftp_alts;
    /**
     * @var callable function used for preparing data set
     * @see IFileRepository::prepare
     */
    private $prepareFunction = null;

    /**
     * @copydoc IFileRepository::__construct
     *
     * @throws SystemException 'ERR_MISSING_MEDIA_FTP_CONFIG'
     * @throws SystemException 'ERR_MISSING_ALTS_FTP_CONFIG'
     */
    public function __construct($id, $base) {
        $this->setId($id);
        $this->setBase($base);

        $cfg_ftp_media = E()->getConfigValue('repositories.ftp.' . $base . '.media');
        $cfg_ftp_alts = E()->getConfigValue('repositories.ftp.' . $base . '.alts');

        if (empty($cfg_ftp_media)) {
            throw new SystemException('ERR_MISSING_MEDIA_FTP_CONFIG');
        }

        if (empty($cfg_ftp_alts)) {
            throw new SystemException('ERR_MISSING_ALTS_FTP_CONFIG');
        }

        $this->ftp_media = new FTP(
            $cfg_ftp_media['server'],
            $cfg_ftp_media['port'],
            $cfg_ftp_media['username'],
            $cfg_ftp_media['password']
        );

        $this->ftp_alts = new FTP(
            $cfg_ftp_alts['server'],
            $cfg_ftp_alts['port'],
            $cfg_ftp_alts['username'],
            $cfg_ftp_alts['password']
        );
    }

    public function getName() {
        return 'ftp';
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function getId() {
        return $this->id;
    }

    public function setBase($base) {
        $this->base = $base;
        return $this;
    }

    public function getBase() {
        return $this->base;
    }

    public function allowsCreateDir() {
        return true;
    }

    public function allowsUploadFile() {
        return true;
    }

    public function allowsEditDir() {
        return true;
    }

    public function allowsEditFile() {
        return true;
    }

    public function allowsDeleteDir() {
        return true;
    }

    public function allowsDeleteFile() {
        return true;
    }

    public function uploadFile($sourceFilename, $destFilename) {
        try {
            $base = $this->getBase() . '/';
            if (strpos($destFilename, $base) === 0) {
                $destFilename = substr($destFilename, strlen($base));
            }
            $this->ftp_media->connect();
            $result = $this->ftp_media->uploadFile($sourceFilename, $destFilename);
            $fi = false;
            if ($result) {
                $fi = $this->analyze($sourceFilename);
            }
            $this->ftp_media->disconnect();
            return $fi;
        } catch (\Exception $e) {
            return false;
        }
    }
    public function putFile($fileData, $filePath) {
        try {
            $base = $this->getBase() . '/';
            if (strpos($filePath, $base) === 0) {
                $destFilename = substr($filePath, strlen($base));
            }
            $this->ftp_media->connect();
            if (!file_put_contents($filePath2 = FileRepository::TEMPORARY_DIR.basename($destFilename), $fileData)) {
                throw new SystemException('ERR_PUT_FILE', SystemException::ERR_CRITICAL, $filePath2);
            }
            $result = $this->ftp_media->uploadFile($filePath2, $filePath);
            $fi = false;
            if ($result) {
                $fi = $this->analyze($filePath);
            }
            $this->ftp_media->disconnect();
            return $fi;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function uploadAlt($sourceFilename, $destFilename, $width, $height) {
        $destFilename = str_replace(
            array('[width]', '[height]', '[upl_path]'),
            array($width, $height, $destFilename),
            self::IMAGE_ALT_CACHE
        );

        try {
            $this->ftp_alts->connect();
            $result = $this->ftp_alts->uploadFile($sourceFilename, $destFilename);
            $fi = false;
            if ($result) {
                $fi = $this->analyze($sourceFilename);
            }
            $this->ftp_alts->disconnect();
            return $fi;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function updateFile($sourceFilename, $destFilename) {
        try {
            $base = $this->getBase() . '/';
            if (strpos($destFilename, $base) === 0) {
                $destFilename = substr($destFilename, strlen($base));
            }
            $this->ftp_media->connect();
            $result = $this->ftp_media->uploadFile($sourceFilename, $destFilename);
            $fi = false;
            if ($result) {
                $fi = $this->analyze($sourceFilename);
            }
            $this->ftp_media->disconnect();
            return $fi;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function updateAlt($sourceFilename, $destFilename, $width, $height) {
        $destFilename = str_replace(
            array('[width]', '[height]', '[upl_path]'),
            array($width, $height, $destFilename),
            self::IMAGE_ALT_CACHE
        );

        try {
            $this->ftp_alts->connect();
            $result = $this->ftp_alts->uploadFile($sourceFilename, $destFilename);
            $fi = false;
            if ($result) {
                $fi = $this->analyze($sourceFilename);
            }
            $this->ftp_alts->disconnect();
            return $fi;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @copydoc IFileRepository::deleteFile
     *
     * @throws SystemException 'ERR_UNIMPLEMENTED_YET'
     *
     * @attention This is not yet implemented!
     */
    public function deleteFile($filename) {
        throw new SystemException('ERR_UNIMPLEMENTED_YET');
    }

    /**
     * @copydoc IFileRepository::deleteAlt
     *
     * @throws SystemException 'ERR_UNIMPLEMENTED_YET'
     *
     * @attention This is not yet implemented!
     */
    public function deleteAlt($filename, $width, $height) {
        throw new SystemException('ERR_UNIMPLEMENTED_YET');
    }

    public function analyze($filename) {
        $fi = E()->FileRepoInfo->analyze($filename, true);
        if (is_object($fi)) {
            $fi->ready = true;
        }
        return $fi;
    }

    public function createDir($dir) {
        $initially_connected = $this->ftp_media->connected();

        try {

            $base = $this->getBase() . '/';
            if (strpos($dir, $base) === 0) {
                $dir = substr($dir, strlen($base));
            }

            if (!$initially_connected) {
                $this->ftp_media->connect();
            }

            $this->ftp_media->createDir($dir);

            if (!$initially_connected) {
                $this->ftp_media->disconnect();
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @copydoc IFileRepository::renameDir
     *
     * @throws SystemException 'ERR_UNIMPLEMENTED_YET'
     *
     * @attention This is not yet implemented!
     */
    public function renameDir($dir) {
        throw new SystemException('ERR_UNIMPLEMENTED_YET');
    }

    /**
     * @copydoc IFileRepository::deleteDir
     *
     * @throws SystemException 'ERR_UNIMPLEMENTED_YET'
     *
     * @attention This is not yet implemented!
     */
    public function deleteDir($dir) {
        throw new SystemException('ERR_UNIMPLEMENTED_YET');
    }

    /**
     * @copydoc IFileRepository::setPrepareFunction
     * @throws SystemException 'ERR_BAD_PREPARE_FUNCTION'
     */
    public function setPrepareFunction($func) {
        if (!is_callable($func)) {
            throw new SystemException('ERR_BAD_PREPARE_FUNCTION');
        }
        $this->prepareFunction = $func;
    }

    /**
     * @copydoc IFileRepository::prepare
     *
     */
    public function prepare(&$data) {
        if ($data && $this->prepareFunction) {
            array_walk($data, $this->prepareFunction);
        }
    }
}
