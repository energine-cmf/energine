<?php
/**
 * @file
 * FileRepositoryRO
 *
 * It contains the definition to:
 * @code
class FileRepositoryRO;
@endcode
 *
 * @author Andy Karpov <andy.karpov@gmail.com>
 * @copyright Energine 2013
 *
 * @version 1.0.0
 */

/**
 * Read-only file repository.
 *
 * @code
class FileRepositoryRO;
@endcode
 *
 * This is used for cases when the file loading realized from outside scripts.
 * It allows only to upload @c alt-files.
 *
 * <b>Rights table</b>
 * <table>
 *      <tr>
 *          <td><b>Action</b></td>
 *          <td><b>Allowed?</b></td>
 *      </tr>
 *      <tr>
 *          <td>CreateDir</td>
 *          <td>false</td>
 *      </tr>
 *      <tr>
 *          <td>UploadFile</td>
 *          <td>false</td>
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
 *          <td>false</td>
 *      </tr>
 *      <tr>
 *          <td>DeleteFile</td>
 *          <td>false</td>
 *      </tr>
 * </table>
 */
class FileRepositoryRO extends FileRepositoryLocal implements IFileRepository {
    public function getName() {
        return 'ro';
    }

    public function allowsCreateDir() {
        return false;
    }

    public function allowsUploadFile() {
        return false;
    }

    public function allowsEditDir() {
        return true;
    }

    public function allowsEditFile() {
        return true;
    }

    public function allowsDeleteDir() {
        return false;
    }

    public function allowsDeleteFile() {
        return false;
    }

    public function uploadFile($sourceFilename, $destFilename) {
        throw new SystemException('ERR_REPOSITORY_READ_ONLY', SystemException::ERR_WARNING, $destFilename);
    }

    public function uploadAlt($sourceFilename, $destFilename, $width, $height) {
        $destFilename = str_replace(
            array('[width]', '[height]', '[upl_path]'),
            array($width, $height, $destFilename),
            self::IMAGE_ALT_CACHE
        );

        $dir = dirname($destFilename);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        if (!copy($sourceFilename, $destFilename)) {
            throw new SystemException('ERR_COPY_UPLOADED_FILE', SystemException::ERR_CRITICAL, $destFilename);
        }

        return $this->analyze($destFilename);
    }

    public function updateFile($sourceFilename, $destFilename) {
        throw new SystemException('ERR_REPOSITORY_READ_ONLY', SystemException::ERR_WARNING, $destFilename);
    }

    public function updateAlt($sourceFilename, $destFilename, $width, $height) {
        throw new SystemException('ERR_REPOSITORY_READ_ONLY', SystemException::ERR_WARNING, $destFilename);
    }

    public function deleteFile($filename) {
        throw new SystemException('ERR_REPOSITORY_READ_ONLY', SystemException::ERR_WARNING, $filename);
    }

    public function deleteAlt($filename, $width, $height) {
        throw new SystemException('ERR_REPOSITORY_READ_ONLY', SystemException::ERR_WARNING, $filename);
    }

    public function createDir($dir) {
        throw new SystemException('ERR_REPOSITORY_READ_ONLY', SystemException::ERR_WARNING, $dir);
    }

    public function renameDir($dir) {
        throw new SystemException('ERR_REPOSITORY_READ_ONLY', SystemException::ERR_WARNING, $dir);
    }

    public function deleteDir($dir) {
        throw new SystemException('ERR_REPOSITORY_READ_ONLY', SystemException::ERR_WARNING, $dir);
    }

}
