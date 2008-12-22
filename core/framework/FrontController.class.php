<?php

/**
 * Класс FrontController.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 * @copyright ColoCall 2006
 * @version $Id$
 */

//require_once('core/framework/DBWorker.class.php');
//require_once('core/framework/SystemException.class.php');
//require_once('core/framework/DocumentController.class.php');
//require_once('core/framework/UserSession.class.php');
/**
 * Front Controller - единая точка входа для запуска работы системы.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 * @final
 */
final class FrontController extends DBWorker {

    /**
     * Конструктор класса.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        try {
            UserSession::getInstance()->start();
            parent::__construct();
        }
        catch (SystemException $systemException) {
            $systemException->handle();
        }
        catch (Exception $generalException) {
            $systemException = new SystemException(
                $generalException->getMessage(),
                $generalException->getCode()
            );
            $systemException->handle();
        }
    }

    /**
     * Создаёт объект DocumentController и передаёт ему управление.
     *
     * @access public
     * @return void
     */
    public function run() {
        try {
            $documentConroller = new DocumentController;
            $documentConroller->run();
        }
        catch (SystemException $systemException) {
            $systemException->handle();
        }
        catch (Exception $generalException) {
            $systemException = new SystemException(
                $generalException->getMessage(),
                $generalException->getCode()
            );
            $systemException->handle();
        }
    }
}
