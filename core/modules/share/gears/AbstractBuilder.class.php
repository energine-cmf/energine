<?php
/**
 * @file
 * AbstractBuilder.
 *
 * It contains the definition to:
 * @code
abstract class AbstractBuilder;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * Builder.
 *
 * @code
abstract class AbstractBuilder;
@endcode
 *
 * Create XML-document based on meta-data.
 *
 * @abstract
 */
abstract class AbstractBuilder extends DBWorker implements IBuilder {
    /**
     * Meta-data.
     * @var DataDescription $dataDescription
     */
    protected $dataDescription;

    /**
     * Data.
     * @var Data $data
     */
    protected $data;

    /**
     * Result document.
     * @var \DOMDocument $result
     */
    protected $result;

    /**
     * @copydoc DBWorker::__construct
     */
    public function __construct() {
        parent::__construct();

        $this->dataDescription = false;
        $this->data = false;
    }

    /**
     * Set meta-data.
     *
     * @param DataDescription $dataDescription Meta-data.
     */
    public function setDataDescription(DataDescription $dataDescription) {
        $this->dataDescription = $dataDescription;
    }

    /**
     * Set data.
     *
     * @param Data $data Data.
     */
    public function setData(Data $data) {
        $this->data = $data;
    }

    //todo VZ: This always returns true. Why?
    /**
     * Build XML-document.
     *
     * @return boolean
     *
     * @throws SystemException 'ERR_DEV_NO_DATA_DESCRIPTION'
     */
    public function build() {
        $this->result = new \DOMDocument('1.0', 'UTF-8');

        // если отсутствует описание данных - построение невозможно
        if ($this->dataDescription == false) {
            throw new SystemException('ERR_DEV_NO_DATA_DESCRIPTION', SystemException::ERR_DEVELOPER);
        }
        $this->run();
        return ($this->result instanceof \DOMDocument ? true : false);
    }

    /**
     * Get result document.
     * @return DOMNode
     */
    public function getResult() {
        return $this->result->documentElement;
    }

    /**
     * Run building.
     *
     * @note This is used in the derived classes to build a result. That result should be stored in Builder::$result
     */
    protected function run() {
    }

    /**
     * Create data filed XML-description.
     *
     * @param string $fieldName Field name.
     * @param FieldDescription $fieldInfo Filed description.
     * @param mixed $fieldValue Field value.
     * @param mixed $fieldProperties Field properties.
     * @return \DOMNode
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

        if($fieldInfo->getMode() == FieldDescription::FIELD_MODE_READ){
            $fieldInfo->removeProperty('message');
            $fieldInfo->removeProperty('pattern');
        }

        if (in_array($fieldInfo->getType(), array(FieldDescription::FIELD_TYPE_FILE))) {
            if (
                (E()->getDocument()->getRights() > ACCESS_READ)
                &&
                ($fieldInfo->getMode() > ACCESS_READ)
            ) {
                E()->getDocument()->addTranslation('TXT_CLEAR');
                E()->getDocument()->addTranslation('BTN_QUICK_UPLOAD');

                $quick_upload_path = $this->getConfigValue('repositories.quick_upload_path', 'uploads/public');
                $quick_upload_pid = $this->dbh->getScalar('SELECT upl_id FROM share_uploads WHERE upl_path=%s LIMIT 1', $quick_upload_path);
                $quick_upload_enabled = true;

                if($quick_upload_pid){
                    $result->setAttribute('quickUploadPath', $quick_upload_path);
                    $result->setAttribute('quickUploadPid', $quick_upload_pid);
                    $result->setAttribute('quickUploadEnabled', $quick_upload_enabled);
                }
            }
            if ($fieldValue) {
                $repoPath = E()->FileRepoInfo->getRepositoryRoot($fieldValue);
                $is_secure = (E()->getConfigValue('repositories.ftp.' . $repoPath . '.secure', 0)) ? true : false;
                $result->setAttribute('secure', $is_secure);

                try {
                    if ($info = E()->FileRepoInfo->analyze($fieldValue)) {
                        $result->setAttribute('media_type', $info->type);
                        $result->setAttribute('mime', $info->mime);
                        $playlist = array();
                        $base = pathinfo($fieldValue, PATHINFO_DIRNAME) . '/' . pathinfo($fieldValue, PATHINFO_FILENAME);

                        if ($info->is_mp4) {
                            $playlist[] = $base . '.mp4';
                        }
                        if ($info->is_webm) {
                            $playlist[] = $base . '.webm';
                        }
                        if ($info->is_flv) {
                            $playlist[] = $base . '.flv';
                        }

                        if ($playlist) {
                            $result->setAttribute('playlist', implode(',', $playlist));
                        }
                    }
                } catch (SystemException $e) {

                }
            }
        }
        elseif(($fieldInfo->getType() == FieldDescription::FIELD_TYPE_SMAP_SELECTOR) && $fieldValue){
            $result->setAttribute('smap_name', E()->getSiteManager()->getSiteByPage($fieldValue)->name.' : '.$this->dbh->getScalar('share_sitemap_translation', 'smap_name', array('smap_id' => $fieldValue, 'lang_id' => E()->getLanguage()->getCurrent())));
        }
        elseif ($fieldInfo->getType() == FieldDescription::FIELD_TYPE_CAPTCHA) {
            require_once(CORE_DIR . '/modules/share/gears/recaptchalib.php');
            $fieldValue = recaptcha_get_html($this->getConfigValue('recaptcha.public'));
        }
        elseif($fieldInfo->getType() == FieldDescription::FIELD_TYPE_VALUE){
            $value = $this->result->createElement('value', $fieldValue['value']);
            $value->setAttribute('id', $fieldValue['id']);
            $fieldValue = $value;
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
     * Create field value.
     *
     * The value is processed and stored in passed DOM-element.
     *
     * @param \DOMElement $result DOM-element.
     * @param FieldDescription $fieldInfo Field description.
     * @param $fieldValue Field value.
     * @return \DOMElement
     */
    protected function buildFieldValue(\DOMElement $result, FieldDescription $fieldInfo, $fieldValue) {
        if (($fieldValue instanceof \DOMNode) ||
            ($fieldValue instanceof \DOMElement)
        ) {
            try {
                $result->appendChild($fieldValue);
            } catch (\Exception $e) {
                $result->appendChild($this->result->importNode($fieldValue, true));
            }
        } elseif ($fieldInfo->getType() ==
            FieldDescription::FIELD_TYPE_TEXTBOX_LIST
        ) {
            if ($fieldValue = $this->createTextBoxItems($fieldValue)) {
                try {
                    $result->appendChild($fieldValue);
                } catch (\Exception $e) {
                    $result->appendChild($this->result->importNode($fieldValue, true));
                }
            }
        } elseif (($fieldInfo->getType() == FieldDescription::FIELD_TYPE_MEDIA) &&
            $fieldValue
        ) {
            try {
                $result->nodeValue = $fieldValue;
                if ($info = E()->FileRepoInfo->analyze($fieldValue)) {
                    $result->setAttribute('media_type', $info->type);
                    $result->setAttribute('mime', $info->mime);
                }
            } catch (SystemException $e) {

            }
        }
        elseif ($fieldValue !== false) {
            // empty() не пропускает значиния 0 и '0'
            if (!empty($fieldValue) || ($fieldValue === 0) || ($fieldValue === '0')) {
                switch ($fieldInfo->getType()) {
                    case FieldDescription::FIELD_TYPE_DATETIME:
                    case FieldDescription::FIELD_TYPE_DATE:
                    case FieldDescription::FIELD_TYPE_TIME:
                        $result->setAttribute('date', $fieldValue);
                        $fieldValue =
                            self::enFormatDate($fieldValue, $fieldInfo->getPropertyValue('outputFormat'), $fieldInfo->getType());

                        break;
                    case FieldDescription::FIELD_TYPE_STRING:
                    case FieldDescription::FIELD_TYPE_TEXT:
                    case FieldDescription::FIELD_TYPE_HTML_BLOCK:
                        //$fieldValue = str_replace('&', '&amp;', $fieldValue);
                        break;

                    default: // not used
                }
                $result->appendChild(new \DomText($fieldValue));
            }

        }

        return $result;
    }

    /**
     * Fix URL.
     *
     * @param string $url URL.
     * @return string
     */
    protected function fixUrl($url) {
        return str_replace(
            array('%2F', '+'),
            array('/', '%20'),
            urlencode($url)
        );
    }

    //todo VZ: What '%q' means?
    /**
     * Format date.
     *
     * Pseudo modifiers:
     * - %E - Today|Yesterday|Tomorrow|After tomorrow|Weekday_abbreviation $Date $month_name, $time[$Year(if not current)]
     * - %f - Weekday name $Date $month_name, $time[$Year(if not current)]
     * - %o - [Today,] $Date $month_name, $time[$Year(if not current)]
     * - %q
     *
     * @param int $date Timestamp.
     * @param string $format Format.
     * @param string $type Type.
     * @return string
     */
    static public function enFormatDate($date, $format, $type = FieldDescription::FIELD_TYPE_DATE) {
        if (!$date) return '';

        $date = strtotime($date);
        if (!in_array($format, array('%E', '%f', '%o', '%q'))) {
            $result = @strftime($format, $date);
            if (!$result) {
                $result = $date;
            }
        } else {
            $result = '';
            $today = strtotime("midnight");
            $tomorrow = strtotime("midnight +1 day");
            $dayAfterTomorrow = strtotime("midnight +2 day");
            $tomorrowPlus3 = strtotime("midnight +3 day");
            $yesterday = strtotime("midnight -1 day");
            $beforeYesterday = strtotime("midnight -2 day");
            switch ($format) {
                case '%E':
                    if ($date >= $today and $date < $tomorrow) {
                        $result .= DBWorker::_translate('TXT_TODAY');
                    } elseif ($date < $today and $date >= $yesterday) {
                        $result .= DBWorker::_translate('TXT_YESTERDAY');
                    } elseif ($date < $yesterday and $date >= $beforeYesterday) {
                        $result .= DBWorker::_translate('TXT_BEFORE_YESTERDAY');
                    } elseif ($date >= $tomorrow && $date < $dayAfterTomorrow) {
                        $result .= DBWorker::_translate('TXT_TOMORROW');
                    } elseif ($date >= $dayAfterTomorrow && $date < $tomorrowPlus3) {
                        $result .= DBWorker::_translate('TXT_AFTER_TOMORROW');
                    } else {
                        $dayNum = date('w', $date);
                        if ($dayNum == 0) {
                            $dayNum = 7;
                        }
                        $result .= DBWorker::_translate('TXT_WEEKDAY_SHORT_' . $dayNum);
                    }
                    $result .= ', ' . date('j', $date) . ' ' . (DBWorker::_translate('TXT_MONTH_' . date('n', $date)));
                    if (date('Y', $date) != date('Y')) {
                        $result .= ' ' . date('Y', $date);
                    }
                    break;
                case '%f':
                    $dayNum = date('w', $date);
                    if ($dayNum == 0) {
                        $dayNum = 7;
                    }
                    $result .= DBWorker::_translate('TXT_WEEKDAY_' . $dayNum) . ', ' . date('j', $date) . ' ' . (DBWorker::_translate('TXT_MONTH_' . date('n', $date)));
                    if (date('Y', $date) != date('Y')) {
                        $result .= ' ' . date('Y', $date);
                    }
                    break;
                case '%o':
                    if ($date >= $today and $date < $tomorrow) {
                        $result .= DBWorker::_translate('TXT_TODAY') . ', ';
                    }
                    $result .= date('j', $date) . ' ' . (DBWorker::_translate('TXT_MONTH_' . date('n', $date)));

                    if (date('Y', $date) != date('Y')) {
                        $result .= ' ' . date('Y', $date);
                    }
                    break;
                case '%q':
                    $result .= date('j', $date) . ' ' . (DBWorker::_translate('TXT_MONTH_' . date('n', $date)));

                    if (date('Y', $date) != date('Y')) {
                        $result .= ' ' . date('Y', $date);
                    }
                    break;
            }
            //Если часы и минуты = 0, считаем что это просто дата, без времени
            if (in_array($type, array(FieldDescription::FIELD_TYPE_DATETIME, FieldDescription::FIELD_TYPE_TIME, FieldDescription::FIELD_TYPE_HIDDEN))) {
                $result .= ', ';
                $result .= date('G', $date) . ':' . date('i', $date);
            }
        }
        return $result;
    }


    /**
     * Create the set of possible field values with type 'select'.
     *
     * @param FieldDescription $fieldInfo Field description.
     * @param mixed $data Data.
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
     * Create the set of field values with type 'textbox'.
     *
     * @param array $data Data.
     * @return mixed
     */
    protected function createTextBoxItems($data = array()) {
        $fieldValue = false;

        if ($data === false) {
            return false;
        } elseif (!is_array($data)) {
            $data = array($data);
        }

        if (!empty($data)) {
            $fieldValue = $this->result->createElement('items');
            foreach ($data as $itemId => $itemData) {
                $item = $this->result->createElement('item', (string)$itemData);
                $item->setAttribute('id', $itemId);
                $fieldValue->appendChild($item);
            }
        }
        return $fieldValue;
    }
}
