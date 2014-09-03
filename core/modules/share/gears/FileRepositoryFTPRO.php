<?php
/**
 * @file
 * FileRepositoryFTPRO.
 *
 * It contains the definition to:
 * @code
class FileRepositoryFTPRO;
@endcode
 *
 * @author Andy Karpov <andy.karpov@gmail.com>
 * @copyright Energine 2013
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * File loader for remote FTP repositories, that is read-only.
 *
 * @code
class FileRepositoryFTPRO;
@endcode
 *
 * Only @c alt-files are accepted for upload.
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
class FileRepositoryFTPRO extends FileRepositoryFTP {
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

    /**
     * @copydoc IFileRepository::uploadFile
     *
     * @throws SystemException ERR_READ_ONLY_FTP_REPO
     */
    public function uploadFile($sourceFilename, $destFilename) {
        throw new SystemException('ERR_READ_ONLY_FTP_REPO');
    }

    /**
     * @copydoc IFileRepository::updateFile
     *
     * @throws SystemException ERR_READ_ONLY_FTP_REPO
     */
    public function updateFile($sourceFilename, $destFilename) {
        throw new SystemException('ERR_READ_ONLY_FTP_REPO');
    }
}
