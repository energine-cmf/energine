<?php

/**
 * Класс AbstractBuilder.
 *
 * @package energine
 * @subpackage kernel
 * @author dr.Pavka
 * @copyright Energine 2006
 */


/**
 * Построитель.
 * Создаёт XML-документ основываясь на переданных ему данных и мета-данных.
 *
 * @package energine
 * @subpackage kernel
 * @author dr.Pavka
 * @abstract
 */
abstract class AbstractBuilder extends DBWorker implements IBuilder{

    /**
     * @access protected
     * @var DataDescription мета-данные
     */
    protected $dataDescription;

    /**
     * @access protected
     * @var Data данные
     */
    protected $data;

    /**
     * @access protected
     * @var DOMDocument результирующий документ
     */
    protected $result;

    /**
     * Конструктор класса.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        parent::__construct();

        $this->dataDescription = false;
        $this->data = false;
    }

    /**
     * Устанавливает мета-данные.
     *
     * @access public
     * @param DataDescription $dataDescription мета-данные
     * @return void
     */
    public function setDataDescription(DataDescription $dataDescription) {
        $this->dataDescription = $dataDescription;
    }

    /**
     * Устанавливает данные.
     *
     * @access public
     * @param Data $data данные
     * @return void
     */
    public function setData(Data $data) {
        $this->data = $data;
    }

    /**
     * Создаёт результирующий XML-документ.
     *
     * @access public
     * @return boolean
     */
    public function build() {
        $this->result = new DOMDocument('1.0', 'UTF-8');

        // если отсутствует описание данных - построение невозможно
        if ($this->dataDescription == false) {
            throw new SystemException('ERR_DEV_NO_DATA_DESCRIPTION', SystemException::ERR_DEVELOPER);
        }
        $this->run();
        return ($this->result instanceof DOMDocument ? true : false);
    }

    /**
     * Возвращает результат работы построителя
     *
     * @access public
     * @return DOMNode
     */
    public function getResult() {
        return $this->result->documentElement;
    }

    /**
     * Используется в производных классах для построения результата.
     * Результат должен быть записан в Builder::$result.
     *
     * @access protected
     * @return void
     */
    protected function run() {
    }

    /**
     * Создаёт XML-описание поля данных.
     *
     * @access protected
     * @param string $fieldName
     * @param FieldDescription $fieldInfo
     * @param mixed $fieldValue
     * @param mixed $fieldProperties
     * @return DOMNode
     */
    protected function createField($fieldName, FieldDescription $fieldInfo, $fieldValue = false, $fieldProperties = false) {
        $result = $this->result->createElement('field');
        $result->setAttribute('name', $fieldName);
        $result->setAttribute('type', $fieldInfo->getType());
        $length = $fieldInfo->getLength();
        if ($length !== true) {
            $result->setAttribute('length', $length);
        }
        $result->setAttribute('mode', $fieldInfo->getMode());

        if ($fieldInfo->getType() == FieldDescription::FIELD_TYPE_FILE) {
            $fieldInfo->setProperty('additionalTitle', $this->translate('MSG_LOAD_FILE'));

        }
        elseif (in_array($fieldInfo->getType(), array(FieldDescription::FIELD_TYPE_HTML_BLOCK, FieldDescription::FIELD_TYPE_TEXT))) {
            $fieldInfo->setProperty('msgOpenField', $this->translate('TXT_OPEN_FIELD'));
            $fieldInfo->setProperty('msgCloseField', $this->translate('TXT_CLOSE_FIELD'));
        }
        elseif($fieldInfo->getType() == FieldDescription::FIELD_TYPE_CAPTCHA){
            require_once(CORE_DIR.'/kernel/recaptchalib.php');
            $fieldValue = recaptcha_get_html($this->getConfigValue('recaptcha.public'));
        }

        foreach ($fieldInfo as $propName => $propValue) {
            if ($propValue && !is_array($propValue)) {
                $result->setAttribute($propName, $propValue);
            }
        }
        if ($fieldProperties) {
            foreach ($fieldProperties as $propName => $propValue) {
                $result->setAttribute($propName, $propValue);
            }
        }

        return $this->buildFieldValue($result, $fieldInfo, $fieldValue);
    }

    /**
     * Создание значения поля
     * Значение обрабатывается и записывается в переданный DOMElement
     *
     * @param DOMElement $result
     * @param FieldDescription $fieldInfo
     * @param  $fieldValue
     *
     * @return DOMElement
     */
    protected function buildFieldValue(DOMElement $result, FieldDescription $fieldInfo, $fieldValue) {
        if (($fieldValue instanceof DOMNode) ||
                ($fieldValue instanceof DOMElement)) {
            try {
                $result->appendChild($fieldValue);
            }
            catch (Exception $e) {
                $result->appendChild($this->result->importNode($fieldValue, true));
            }
        }
        elseif ($fieldInfo->getType() ==
                FieldDescription::FIELD_TYPE_TEXTBOX_LIST) {
            $fieldValue = $this->createTextBoxItems($fieldValue);
            try {
                $result->appendChild($fieldValue);
            }
            catch (Exception $e) {
                $result->appendChild($this->result->importNode($fieldValue, true));
            }
        }
        elseif (($fieldInfo->getType() == FieldDescription::FIELD_TYPE_MEDIA) &&
                $fieldValue) {
            try {
                if ($info = E()->FileInfo->analyze($fieldValue)) {
                    $el = $this->result->createElement($info->type);
//                    $el->nodeValue = $fieldValue;
                    $el->nodeValue = $this->fixUrl($fieldValue);
                    switch ($info->type) {
                        case FileInfo::META_TYPE_IMAGE:
                            $el->setAttribute('width', $info->width);
                            $el->setAttribute('height', $info->height);
//                            $el->setAttribute('image', $fieldValue);
                            $el->setAttribute('image', $this->fixUrl($fieldValue));
                            break;
                        case FileInfo::META_TYPE_VIDEO:
                            $el->setAttribute('image', FileObject::getVideoImageFilename($fieldValue));
                            break;
                        default:
                            break;
                    }
                    $result->appendChild($el);
                    if($this->getConfigValue('thumbnails'))
                    foreach ($this->getConfigValue('thumbnails') as $thumbName => $thumbnail) {
                        $thumbnailFile =
                                FileObject::getThumbFilename(
                                    $fieldValue,
                                    $width = (int) $thumbnail['width'],
                                    $height = (int) $thumbnail['height']
                                );
//                        if (!file_exists($thumbnailFile)) {
//                            $thumbnailFile = (string)$thumbnail->gag;
//                        }
                        $img = $this->result->createElement(
                            'thumbnail'
                        );
                        $img->setAttribute('width', $width);
                        $img->setAttribute('height', $height);
                        $img->setAttribute('name', $thumbName);
//                        $img->nodeValue = $thumbnailFile;
                        $img->nodeValue = $this->fixUrl($thumbnailFile);
                        $result->appendChild($img);
                    }
                }
            }
            catch (SystemException $e) {

            }
        }
        elseif ($fieldValue !== false) {
            if (!empty($fieldValue)) {
                switch ($fieldInfo->getType()) {
                    case FieldDescription::FIELD_TYPE_DATETIME:
                    case FieldDescription::FIELD_TYPE_DATE:
                    case FieldDescription::FIELD_TYPE_TIME:
                    case FieldDescription::FIELD_TYPE_HIDDEN:
                        try {
                            if(is_long($fieldValue)){
                            $result->setAttribute('date', @strftime('%d-%m-%Y-%H-%M-%S', $fieldValue));
                            $fieldValue =
                                    self::enFormatDate($fieldValue, $fieldInfo->getPropertyValue('outputFormat'), $fieldInfo->getType());
                            }
                        }
                        catch (Exception  $dummy) {

                        }
                        ;
                        break;
                    case FieldDescription::FIELD_TYPE_STRING:
                    case FieldDescription::FIELD_TYPE_TEXT:
                    case FieldDescription::FIELD_TYPE_HTML_BLOCK:
                        $fieldValue = str_replace('&', '&amp;', $fieldValue);
                        break;

                    default: // not used
                }

                $result->nodeValue = $fieldValue;
            }

        }

        return $result;
    }

    /**
     * @param  string $url
     * @return string
     */
    protected function fixUrl($url){
        return str_replace(
            array('%2F', '+'),
            array('/', '%20'),
            urlencode($url)
        );
    }

    /**
     * Форматирование даты
     *
     * @param int $date timstamp
     * @param string $format
     *
     * @return string
     * @access public
     * @static
     */
    static public function enFormatDate($date, $format, $type = FieldDescription::FIELD_TYPE_DATE) {
        $date = intval($date);
        if ($format != '%E') {
            $result = @strftime($format, $date);
            if (!$result) {
                $result = $date;
            }
        }
        else {
            $result = '';

            $today = strtotime("midnight");
            $tomorrow = strtotime("midnight +1 day");
            $dayAfterTomorrow = strtotime("midnight +2 day");
            $yesterday = strtotime("midnight -1 day");

            if($date >= $today and $date < $tomorrow){
                $result .= DBWorker::_translate('TXT_TODAY');
            }
            elseif($date < $today and $date >= $yesterday){
                $result .= DBWorker::_translate('TXT_YESTERDAY');
            }
            elseif($date>=$tomorrow && $date<$dayAfterTomorrow){
                $result .= DBWorker::_translate('TXT_TOMORROW');
            }
            else{
                $result .= date('j', $date) . ' ' . (DBWorker::_translate('TXT_MONTH_' . date('n', $date)));
                if(date('Y',$date) != date('Y')){
                    $result .= ' ' . date('Y', $date);
                }
            }
            //Если часы и минуты = 0, считаем что это просто дата, без времени
            if(in_array($type, array(FieldDescription::FIELD_TYPE_DATETIME, FieldDescription::FIELD_TYPE_TIME, FieldDescription::FIELD_TYPE_HIDDEN))){
                $result .= ', ';
                $result .= date('G', $date) . ':' . date('i', $date);
            }
        }

        return $result;
    }


    /**
     * Создает набор возможных значений поля типа select.
     *
     * @access protected
     * @param FieldDescription $fieldInfo
     * @param mixed $data
     * @return DOMNode
     */
    protected function createOptions(FieldDescription $fieldInfo, $data = false) {
        $fieldValue = $this->result->createElement('options');
        if (is_array($fieldInfo->getAvailableValues()))
            foreach ($fieldInfo->getAvailableValues() as $key => $option) {
                $dom_option =
                        $this->result->createElement('option', str_replace('&', '&amp;', $option['value']));
                $dom_option->setAttribute('id', $key);
                if ($option['attributes']) {
                    foreach ($option['attributes'] as $attrName => $attrValue) {
                        $dom_option->setAttribute($attrName, $attrValue);
                    }
                }
                // для поля типа multi-select
                if (is_array($data) && in_array($key, $data)) {
                    $dom_option->setAttribute('selected', 'selected');
                }
                $fieldValue->appendChild($dom_option);
            }
        return $fieldValue;
    }

    /**
     * Создает набор значений для поля типа textbox
     *
     * @return mixed
     * @access protected
     */
    protected function createTextBoxItems($data = array()) {
        $fieldValue = $this->result->createElement('items');
        if ($data === false) {
            $data = array();
        }
        elseif (!is_array($data)) {
            $data = array($data);
        }

        foreach ($data as $itemData) {
            $item = $this->result->createElement('item', (string) $itemData);
            $fieldValue->appendChild($item);
        }
        return $fieldValue;
    }
}
