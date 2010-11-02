<?php

/**
 * Класс BaseException
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 * @copyright Energine 2006
 * @version $Id$
 */

/**
 * Базовое исключение.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 */
class BaseException extends Exception {

    /**
     * XSLT-документ для страницы ошибки
     */
    const ERROR_TRANSFORMER = 'error_page.xslt';

    /**
     * Критическая ошибка
     */
    const ERR_CRITICAL = 0;

    /**
     * Ошибка 404 - страницы не существует
     */
    const ERR_404 = 1;

    /**
     * Ошибка 403 - нет прав на просмотр страницы
     */
    const ERR_403 = 2;

    /**
     * Ошибка при работе с БД
     */
    const ERR_DB = 3;

    /**
     * Ошибка разработчика, где-то что-то неверно написано :)
     */
    const ERR_DEVELOPER = 4;

    /**
     * Предупреждение
     */
    const ERR_WARNING = 10;

    /**
     * Замечание
     */
    const ERR_NOTICE = 20;

    /**
     * Ошибка, связанная с мультиязычностью. Возникает при обработке другой
     * ошибки и отсутствия для неё переводов. Без ERR_LANG возможет уход
     * в рекурсию и полный пиздец.
     *
     * Данная ошибка касается исключительно разработчиков системы.
     *
     * @todo сделать хоть что-нибудь! :)
     */
    const ERR_LANG = 5;

    /**
     * Когда режим отладки включен:
     *
     *     1. Можно вывести XML-документ страницы добавив к query-части URI
     *        параметр 'debug'.
     *     2. При обработке системных ошибок выводится максимально подробная
     *        информация о возникшей ошибке.
     *
     * @access private
     * @var boolean флаг режима отладки
     */
    private $isDebugEnabled = true;

    /**
     * @access protected
     * @var Response экземпляр объекта Response
     */
    protected $response;

    /**
     * @access protected
     * @var boolean флаг режима вывода XML-документа страницы
     * @todo плохое имя
     */
    protected $isXML;

    /**
     * @access protected
     * @var DOMDocument
     */
    protected $doc;

    /**
     * @access protected
     * @var mixed дополнительная информация об ошибке
     */
    protected $customMessages = array();

    /**
     * Конструктор класса.
     *
     * @access public
     * @param string $message
     * @param int $code
     * @param mixed $customMessages
     * @return void
     * @todo определиться с $customMessages: это mixed или array?
     */
    public function __construct($message, $code = self::ERR_CRITICAL, $customMessages = null) {
        $this->isDebugEnabled = (bool)Object::_getConfigValue('site.debug');

        $this->response = Response::getInstance();
        $this->response->setHeader('X-Accel-Expires', 0);
        $this->isXML = isset($_GET['debug']);
        $this->doc = new DOMDocument('1.0', 'UTF-8');
        if (isset($customMessages)) {
            if (!is_array($customMessages)) {
            	$this->customMessages = array($customMessages);
            }
            else {
                $this->customMessages = $customMessages;
            }
        }

        if ($code == self::ERR_LANG) {
            $message = DBWorker::_translate($message, Language::getInstance()->getDefault());
        }
        elseif ($code == self::ERR_403) {
            $this->response->setStatus(403);
            $this->response->setHeader('X-Accel-Expires', 900);
            $message = DBWorker::_translate($message, Language::getInstance()->getCurrent());
        }
        elseif ($code == self::ERR_404) {
            $this->response->setStatus(404);
            $this->response->setHeader('X-Accel-Expires', 900);
            $message = DBWorker::_translate($message, Language::getInstance()->getCurrent());
        }
        elseif ($code != self::ERR_DB ) {
            $message = DBWorker::_translate($message, Language::getInstance()->getCurrent());
        }


        parent::__construct($message, $code);
    }

    /**
	 * Возвращает дополнительную информацию об ошибке.
	 *
	 * @access public
	 * @return string
	 * @todo переименовать в getCustomMessages
	 */
    public function getCustomMessage() {
        return $this->customMessages;
    }

    /**
     * Отправляет уведомление о ошибке
     *
     * @return void
     * @access private
     */

    private function sendNotification() {
        $mail = new Mail();
        $projectName = ($fake = Object::_getConfigValue('project.name'))?$fake:$_SERVER['HTTP_HOST'];
        $from = Object::_getConfigValue('mail.from');
        $customMessage = implode("\r\n", $this->getCustomMessage());

        $body = sprintf("Project:%s\r\nCode:%s\r\nMessage:%s\r\nCustomMessage:%s\r\n,File:%s\r\nLine:%s\r\nTrace:%s", $projectName, $this->getCode(), $this->getMessage(), $customMessage,$this->getFile(), $this->getLine(), $this->getTraceAsString());
        $mail->setText($body)
            ->setFrom($from, $from)
            ->addTo(Object::_getConfigValue('mail.feedback'))
            ->setSubject($projectName.' Error Notification:'.$this->getMessage())
            ->send();
    }
    /**
     * Формирует XML-представление ошибки.
     *
     * @access protected
     * @return void
     */
    protected function build() {
        $request = Request::getInstance();

        $dom_errors = $this->doc->createElement('errors');
        $dom_errors->setAttribute('uri', $request->getPath(Request::PATH_WHOLE, true));
        $dom_errors->setAttribute('base', SiteManager::getInstance()->getCurrentSite()->base);
        $dom_errors->setAttribute('debug', $this->isDebugEnabled);

        $dom_error = $this->doc->createElement('error');
        $dom_error->setAttribute('code', $this->getCode());
        $dom_error->setAttribute('file', $this->getFile());
        $dom_error->setAttribute('line', $this->getLine());

        $dom_error->appendChild(
            $this->doc->createElement('message', $this->getMessage())
        );

        $customMessages = $this->getCustomMessage();
        if ($customMessages) {
            $dom_customMessages = $this->doc->createElement('customMessages');
            if (is_array($customMessages)) {
                foreach ($customMessages as $customMessage) {
                    $dom_customMessages->appendChild(
                        $this->doc->createElement('customMessage', $customMessage)
                    );
                }
            }
            else {
            	$dom_customMessages->nodeValue = $customMessages;
            }
            $dom_error->appendChild($dom_customMessages);
        }

        $dom_errors->appendChild($dom_error);
        $this->doc->appendChild($dom_errors);
    }

    /**
	 * Обрабатывает ошибку путём её вывода :)
	 *
	 * @access public
	 * @return void
	 */
    public function handle() {
        /*if (!in_array($this->getCode(), array(self::ERR_403, self::ERR_404, self::ERR_NOTICE, self::ERR_WARNING)) && !$this->isDebugEnabled) {
            $this->sendNotification();
        }*/

        $this->build();

        if ($this->isDebugEnabled && $this->isXML) {
            $this->response->setHeader('Content-Type', 'text/xml; charset=UTF-8');
            $result = $this->doc->saveXML();
        }
        else {
            $this->response->setHeader('Content-Type', 'text/html; charset=UTF-8');
            $transformer = new Transformer;
            $result = $transformer->transform($this->doc, self::ERROR_TRANSFORMER);
        }

        $this->response->write($result);
        $this->response->commit();
    }
}
