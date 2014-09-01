<?php
/**
 * @file
 * SystemException.
 *
 * It contains the definition to:
 * @code
class SystemException;
@endcode
 *
 * @author 1m.dm
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * Base exception.
 *
 * @code
class SystemException;
@endcode
 */
class SystemException extends \Exception {
    /**
     * Critical error.
     * @var int ERR_CRITICAL
     */
    const ERR_CRITICAL = 0;

    /**
     * Error 404 - Page not found.
     * @var int ERR_404
     */
    const ERR_404 = 1;

    /**
     * Error 403 - Forbidden to view the page.
     * @var int ERR_403
     */
    const ERR_403 = 2;

    /**
     * Error by work with data base.
     * @var int ERR_DB
     */
    const ERR_DB = 3;

    /**
     * Developer error.
     * Something somewhere incorrect written :)
     * @var int ERR_DEVELOPER
     */
    const ERR_DEVELOPER = 4;
    /**
     * Cannot find needed class
     * @var int ERR_DEPENDENCY
     */
    const ERR_DEPENDENCY = 5;

    /**
     * Warning.
     * @var int ERR_WARNING
     */
    const ERR_WARNING = 10;

    /**
     * Notice.
     * @var int ERR_NOTICE
     */
    const ERR_NOTICE = 20;

    //todo VZ: What means the todo inside doc?
    /**
     * Error associated with multilanguage.
     * @var int ERR_LANG
     *
     * @attention This error raises by missing of translation for another processed error. Without of this the system can go in unlimited recursion.
     *
     * @note This error refers to system developers.
     *
     * @todo сделать хоть что-нибудь! :)
     */
    const ERR_LANG = 5;


    /**
     * Exemplar of Response object.
     * @var Response $response
     */
    protected $response;


    /**
     * Additional information about error.
     * @var array|string $customMessages
     */
    protected $customMessages = array();
    /**
     * @var array
     */
    private $backtrace = array();

    /**
     * @param string $message Message.
     * @param int $code Error code.
     * @param mixed $customMessages Additional information about error.
     *
     * @todo определиться с $customMessages: это mixed или array?
     */
    public function __construct($message, $code = self::ERR_CRITICAL, $customMessages = null) {
        $this->response = E()->getResponse();
        if (isset($customMessages)) {
            if (!is_array($customMessages)) {
                $this->customMessages = array($customMessages);
            }
            else {
                $this->customMessages = $customMessages;
            }
        }


        if ($code == self::ERR_LANG) {
            $this->response->setStatus(503);
            $this->response->setHeader('Retry-After', 20);
            $message = DBWorker::_translate($message, E()->getLanguage()->getDefault());
        }
        elseif ($code == self::ERR_403) {
            $this->response->setStatus(403);
            $message = DBWorker::_translate($message, E()->getLanguage()->getCurrent());
        }
        elseif ($code == self::ERR_404) {
            $this->response->setStatus(404);
            $message = DBWorker::_translate($message, E()->getLanguage()->getCurrent());
        }
        elseif ($code != self::ERR_DB ) {
            $message = DBWorker::_translate($message, E()->getLanguage()->getCurrent());
        }
        $this->backtrace = debug_backtrace(!DEBUG_BACKTRACE_PROVIDE_OBJECT, 5);

        parent::__construct($message, $code);
    }

    /**
     * Get additional information about error.
     *
     * @return mixed
     *
     * @todo переименовать в getCustomMessages
     */
    public function getCustomMessage() {
        return $this->customMessages;
    }

    /**
     * Set file.
     * It is used to force a change of error location.
     *
     * @param string $file File.
     * @return SystemException
     *
     * @note It is called from nrgnErrorHandler().
     */
    public function setFile($file){
        $this->file = $file;
        return $this;
    }
    /**
     * Set line.
     *
     * @param string $line Line.
     * @return SystemException
     *
     * @see SystemException::setFile
     */
    public function setLine($line){
        $this->line = $line;
        return $this;
    }

    /**
     * @return array
     */
    public function getBacktrace() {
        return $this->backtrace;
    }

}