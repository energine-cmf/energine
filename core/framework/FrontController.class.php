<?php

/**
 * Класс FrontController.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 * @copyright Energine 2006
 * @version $Id$
 */

/**
 * Front Controller - единая точка входа для запуска работы системы.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 * @final
 */
final class FrontController extends DBWorker {

    private static $instance;

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
     * Возвращает единый для всей системы экземпляр класса FrontController
     *
     * @access public
     * @static
     * @return FrontController
     */
    static public function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new FrontController();
        }
        return self::$instance;
    }

    /**
     * Создаёт объект DocumentController и передаёт ему управление.
     *
     * @access public
     * @return void
     */
    public function run() {
        try {
            $documentConroller = DocumentController::getInstance();
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
