<?php

/**
 * Класс FileRepositoryFTPRO
 *
 * @package energine
 * @subpackage kernel
 * @author Andy Karpov <andy.karpov@gmail.com>
 * @copyright Energine 2013
 */


/**
 * Переопределенный класс для замороженных FTP репозитариев, доступ к которым предоставлятся
 * только для чтения, но с возможностью загружать alts
 *
 * @package energine
 * @subpackage kernel
 * @author Andy Karpov <andy.karpov@gmail.com>
 */
class FileRepositoryFTPRO extends FileRepositoryFTP {

    /**
     * Возвращает true, если разрешено создание папок в репозитарии
     *
     * @return boolean
     */
    public function allowsCreateDir() {
        return false;
    }

    /**
     * Возвращает true, если разрешена загрузка файлов в репозитарий
     *
     * @return boolean
     */
    public function allowsUploadFile() {
        return false;
    }

    /**
     * Возвращает true, если разрешено редактирование папки в репозитарии
     *
     * @return boolean
     */
    public function allowsEditDir() {
        return true;
    }

    /**
     * Возвращает true, если разрешено редактирование файла в репозитарии
     *
     * @return boolean
     */
    public function allowsEditFile() {
        return true;
    }

    /**
     * Возвращает true, если разрешено удаление папки из репозитария
     *
     * @return boolean
     */
    public function allowsDeleteDir() {
        return false;
    }

    /**
     * Возвращает true, если разрешено удаление файла из репозитария
     *
     * @return boolean
     */
    public function allowsDeleteFile() {
        return false;
    }

    /**
     * Метод загрузки файла в хранилище
     *
     * @param string $sourceFilename
     * @param string $destFilename
     * @return boolean|object
     * @throws SystemException
     */
    public function uploadFile($sourceFilename, $destFilename) {
        throw new SystemException('ERR_READ_ONLY_FTP_REPO');
    }
}
