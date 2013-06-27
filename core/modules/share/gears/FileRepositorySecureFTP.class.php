<?php

/**
 * Класс FileRepositorySecureFTP
 *
 * @package energine
 * @subpackage kernel
 * @author Andy Karpov <andy.karpov@gmail.com>
 * @copyright Energine 2013
 */


/**
 * Реализация интерфейса загрузчика файлов для удаленных Secure FTP репозитариев.
 * Используется в случаях, когда загрузка файлов в репозитарий осуществляется средствами админки,
 * но хранилище удаленное, через FTP. При этом на файлы репозитария действуют специфические ограничения
 *
 * @package energine
 * @subpackage kernel
 * @author Andy Karpov <andy.karpov@gmail.com>
 */
class FileRepositorySecureFTP extends FileRepositoryFTP {

    /**
     * Метод получения массива дополнительных флагов репозитария
     *
     * @return array
     */
    public function getFlags() {
        return array('secure' => '1');
    }
}
