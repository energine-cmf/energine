<?php
/**
 * @file
 * Mail.
 *
 * It contains the definition to:
 * @code
final class Mail;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2007
 *
 * @version 1.0.0
 */

namespace Energine\share\gears;

/**
 * Message sender.
 *
 * It contains the definition to:
 * @code
final class Mail;
@endcode
 *
 * @final
 */
final class Mail extends Object {
    /**
     * End Of Line.
     * @var string EOL
     */
    const EOL = "\n";

    /**
     * Mime boundary.
     * @var string $MIMEBoundary
     */
    private $MIMEBoundary;

    /**
     * Message subject.
     * @var string $subject
     */
    private $subject = false;

    /**
     * Sender address.
     * Format: User name <email\@address.ua>
     * @var string $sender
     */
    private $sender;

    /**
     * Set of recipients.
     * @var array $to
     */
    private $to = array();

    /**
     * Message text.
     * @var string $text
     */
    private $text = false;

    /**
     * Message header.
     * @var array $headers
     */
    private $headers = array();

    /**
     * Reply-to.
     * @var string $replyTo
     */
    private $replyTo = array();

    /**
     * Set of attachments.
     * @var array $attachments
     */
    private $attachments = array();

    public function __construct() {
        $this->sender = $this->getConfigValue('mail.from');
    }

    /**
     * Set "from" attribute.
     *
     * @param string $email Email address.
     * @param string|bool $name Name.
     * @return Mail
     */

    public function setFrom($email, $name = false) {
        $this->sender = ($name)?'=?UTF-8?B?'.base64_encode($name).'?=<'.$email.'>':$email;
        return $this;
    }

    /**
     * Set message subject.
     *
     * @param string $subject Subject text.
     * @return Mail
     */
    public function setSubject($subject) {
        $this->subject = '=?UTF-8?B?'.base64_encode(strip_tags($subject)).'?=';
        return $this;
    }

    /**
     * Add recipient.
     *
     * @param string $email Email address.
     * @param string|bool $name Name.
     * @return Mail
     */
    public function addTo($email, $name = false) {
        $email = trim($email);
        $this->to[$email] = ($name)?'=?UTF-8?B?'.base64_encode($name).'?=<'.$email.'>':$email;
        return $this;
    }

    /**
     * Clear recipient list.
     *
     * @return Mail
     */
    public function clearRecipientList(){
        $this->to = array();

        return $this;
    }

    /**
     * Add recipient to Reply-to
     *
     * @param string $email Email address.
     * @param string|bool $name Name.
     * @return Mail
     */
    public function addReplyTo($email, $name = false) {
        $this->replyTo[$email] = ($name)?'=?UTF-8?B?'.base64_encode($name).'?=<'.$email.'>':$email;
        return $this;
    }

    /**
     * Set message text.
     *
     * @param string $text Text.
     * @param mixed $data Data templating.
     * @return Mail
     */
    public function setText($text, $data = false) {
        if ($data) {
            if (is_array($data)) {
                extract($data);
            }
            $host = E()->getSiteManager()->getDefaultSite()->base;
            $errorLevel = error_reporting(E_ERROR);
            $text = addslashes($text);
            eval("\$text = \"$text\";");
            error_reporting($errorLevel);
        }
        $this->text = $text;
        return $this;
    }

    /**
     * Get message text.
     *
     * @return string
     */
    public function getText() {
        return $this->text;
    }

    /**
     * Add attachment.
     *
     * @param mixed $file File.
     * @param string|bool $fileName Filename.
     * @return Mail
     */
    public function addAttachment($file, $fileName = false) {
        if (file_exists($file)) {
            $fileContent = base64_encode((file_get_contents($file)));
            $fileName = (!$fileName)?basename($file):$fileName;
            $this->attachments[$fileName] = $fileContent;
        }

        return $this;
    }


    /**
     * Send message.
     *
     * @return boolean
     */
    public function send() {
        $MIMEBoundary1 = md5(time()).rand(1000,9999);
        $MIMEBoundary2 = md5(time()).rand(1000,9999);

        $this->headers = array('X-Mailer: PHP v'.phpversion());
        $this->headers[] = 'MIME-Version: 1.0';

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