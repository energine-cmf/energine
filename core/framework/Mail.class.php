<?php

/**
 * Содержит класс Mail
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @copyright Energine 2007
 * @version $Id$
 */


/**
 * Отправщик сообщения
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @final
 */
final class Mail extends Object {
    /**
     * End Of Line
     *
     */
    const EOL = "\n";

    /**
     * MimeBoundary
     *
     * @var string
     * @access private
     */
    private $MIMEBoundary;

    /**
     * Тема письма
     *
     * @var string
     * @access private
     */
    private $subject = false;

    /**
     * Адрес отправителя
     * Формат: Имя отправителя <em@il_address>
     * @var string
     * @access private
     */
    private $sender;

    /**
     * Перечень получателей
     *
     * @var array
     * @access private
     */
    private $to = array();

    /**
     * Текст сообщения
     *
     * @var string
     * @access private
     */
    private $text = false;

    /**
     * Заголовки письма
     *
     * @var array
     * @access private
     */
    private $headers = array();

    /**
     * Reply-to
     *
     * @var string
     * @access private
     */
    private $replyTo = array();

    /**
     * Массив файлов для аттачмента
     *
     * @var array
     * @access private
     */
    private $attachments = array();

    /**
     * Конструктор класса
     *
     * @return void
     */
	public function __construct() {
		parent::__construct();
        $this->headers[] = 'X-Mailer: PHP v'.phpversion();
        $this->headers[] = 'MIME-Version: 1.0';
        $this->sender = $this->getConfigValue('mail.from');
	}

	/**
	 * Устанавливает аттрибут от
	 *
	 * @param string $email
	 * @param string $name
	 * @return Mail
	 * @access public
	 */

	public function setFrom($email, $name = false) {
	    $this->sender = ($name)?'=?UTF-8?B?'.base64_encode($name).'?=<'.$email.'>':$email;
	    return $this;
	}

    /**
     * Устанавливает тему письма
     *
     * @param string $subject
     * @return Mail
     * @access public
     */

    public function setSubject($subject) {
        $this->subject = '=?UTF-8?B?'.base64_encode(strip_tags($subject)).'?=';
        return $this;

    }

    /**
     * Добавляет получателя к списку получателей
     *
     * @return Mail
     * @access public
     */

    public function addTo($email, $name = false) {
        $email = trim($email);
        $this->to[$email] = ($name)?'=?UTF-8?B?'.base64_encode($name).'?=<'.$email.'>':$email;
        return $this;
    }
    /**
     * Очищает список получателей
     * 
     * @access public
     * @return Mail
     */
    public function clearRecipientList(){
        $this->to = array();
        
        return $this;
    }
    /**
     * Добавление reply-to заголовка
     *
     * @return Mail
     * @access public
     */

    public function addReplyTo($email, $name = false) {
        $this->replyTo[$email] = ($name)?'=?UTF-8?B?'.base64_encode($name).'?=<'.$email.'>':$email;
        return $this;
    }

    /**
     * Устанавливает текст сообщения
     *
     * @param string $text
     * @param mixed $data данные для шаблонизатора
     * @return Mail
     * @access public
     */

    public function setText($text, $data = false) {
    	if ($data) {
            if (is_array($data)) {
            	extract($data);
            }
        	$errorLevel = error_reporting(E_ERROR);
        	$text = addslashes($text);
        	eval("\$text = \"$text\";");
        	error_reporting($errorLevel);
        }
        $this->text = $text;
        return $this;
    }

    /**
     * Возвращает текст сообщения
     *
     * @return string
     * @access public
     */

    public function getText() {
        return $this->text;
    }

    /**
     * Добавить аттач
     *
     * @param mixed $file
     * @return void
     * @access public
     */

    public function addAttachment($file, $fileName = false) {
        if (file_exists($file)) {
			$fileContent = base64_encode(stripslashes(file_get_contents($file)));
	        $fileName = (!$fileName)?basename($file):$fileName;
	        $this->attachments[$fileName] = $fileContent;
        }
        
        return $this;
    }


    /**
     * Отправляет сообщение
     *
     * @return boolean
     * @access public
     */

    public function send() {
        $MIMEBoundary1 = md5(time()).rand(1000,9999);
        $MIMEBoundary2 = md5(time()).rand(1000,9999);

        # Common Headers
        $this->headers[] = 'From: '.$this->sender;
        $this->headers[] = (!empty($this->replyTo))?'Reply-To: '.implode(',', $this->replyTo):'Reply-To: '.$this->sender;
        $this->headers[] = 'Return-Path: '.$this->sender;
        $this->headers[] = "Content-Type: multipart/mixed;
        boundary=\"".$MIMEBoundary1."\"".self::EOL;

        $message = "This is a multi-part message in MIME format.".self::EOL.self::EOL;
        $message .= "--".$MIMEBoundary1.self::EOL;

        $message .= "Content-Type: multipart/alternative;
        boundary=\"".$MIMEBoundary2."\"".self::EOL.self::EOL;

        # Text Version
        $message .= "--".$MIMEBoundary2.self::EOL;
        $message .= "Content-Type: text/plain; charset=UTF-8".self::EOL;
        $message .= "Content-Transfer-Encoding: 8bit".self::EOL.self::EOL;
        $message .= strip_tags($this->text).self::EOL.self::EOL;

        # HTML Version
        $message .= "--".$MIMEBoundary2.self::EOL;
        $message .= "Content-Type: text/html; charset=UTF-8".self::EOL;
        $message .= "Content-Transfer-Encoding: 8bit".self::EOL.self::EOL;
        $message .= '<HTML><HEAD><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></HEAD><BODY>'.$this->text.'</BODY></HTML>'.self::EOL.self::EOL;

        # Finished
        $message .= "--".$MIMEBoundary2."--".self::EOL.self::EOL;  // finish with two eol's for better security. see Injection.
        if (!empty($this->attachments)) {
            foreach ($this->attachments as $attachName => $attach) {
                $message .= "--".$MIMEBoundary1.self::EOL;
                $message .= "Content-Type: application/octet-stream; name=\"".$attachName."\"".self::EOL;
                $message .= "Content-Transfer-Encoding: base64".self::EOL;
                $message .= "Content-Disposition: attachment;".self::EOL;
                $message .= "       filename=\"".$attachName."\"".self::EOL.self::EOL;
                $message .= chunk_split($attach).self::EOL;
            }
        }

        $message .= "--".$MIMEBoundary1."--";
        $headers = implode(self::EOL, $this->headers);

        if(!empty($this->to)) {
            $result = mail(implode(',', $this->to), $this->subject, $message, $headers);
        }
        else {
        	$result = false;
        }

        return $result;
    }
}