<?php
/**
 * @file
 * Builder.
 *
 * It contains the definition to:
 * @code
abstract class Builder;
 * @endcode
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
abstract class Builder;
 * @endcode
 *
 * Create XML-document based on meta-data.
 *
 * @abstract
 */
class Builder extends XMLBuilder {
    use DBWorker, DataBuilderWorker;
    /**
     * Title.
     * @var string $title
     */
    protected $title;

    /**
     * @param string $title Recordset title.
     */
    public function __construct($title = '') {
        parent::__construct();
        $this->title = $title;
    }

    protected function run() {
        if ($this->data->isEmpty() || !$this->data->getRowCount()) {
            $this->getResult()->setAttribute('empty', translate('MSG_EMPTY_RECORDSET'));
        }
        if (!$this->dataDescription->isEmpty()) {
            $rowCount = 0;
            $i = 0;
            do {
                if (!$this->data->isEmpty()) {
                    $rowCount = $this->data->getRowCount();
                }

                $dom_record = $this->document->createElement('record');

                foreach ($this->dataDescription as $fieldName => $fieldInfo) {
                    $fieldProperties = false;
                    if (is_null($fieldInfo->getPropertyValue('tabName'))) {
                        $fieldInfo->setProperty('tabName', $this->title);
                    }

                    // если тип поля предполагает выбор из нескольких значений - создаем соответствующие узлы
                    if (in_array($fieldInfo->getType(), [FieldDescription::FIELD_TYPE_MULTI, FieldDescription::FIELD_TYPE_SELECT])) {
                        if ($this->data && $this->data->getFieldByName($fieldName)) {
                            if ($fieldInfo->getType() == FieldDescription::FIELD_TYPE_SELECT) {
                                $data = [$this->data->getFieldByName($fieldName)->getRowData($i)];
                            } else {
                                $data = $this->data->getFieldByName($fieldName)->getRowData($i);
                            }
                        } else {
                            $data = false;
                        }
                        $fieldValue = $this->createOptions($fieldInfo, $data);
                    } elseif ($this->data->isEmpty()) {
                        $fieldValue = false;
                    } elseif ($this->data->getFieldByName($fieldName)) {
                        $fieldProperties = $this->data->getFieldByName($fieldName)->getRowProperties($i);
                        $fieldValue = $this->data->getFieldByName($fieldName)->getRowData($i);
                    } else {
                        $fieldValue = false;
                    }
                    $dom_field = $this->createField($fieldName, $fieldInfo, $fieldValue, $fieldProperties);
                    $dom_record->appendChild($dom_field);
                }

                $this->getResult()->appendChild($dom_record);
                $i++;
            } while ($i < $rowCount);
        }

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
        $result = $this->document->createElement('field');
        $result->setAttribute('name', $fieldName);
        $result->setAttribute('type', $fieldInfo->getType());
        $length = $fieldInfo->getLength();
        if ($length !== true) {
            $result->setAttribute('length', $length);
        }
        $result->setAttribute('mode', $fieldInfo->getMode());

        if ($fieldInfo->getMode() == FieldDescription::FIELD_MODE_READ) {
            $fieldInfo->removeProperty('message');
            $fieldInfo->removeProperty('pattern');
        }

        if (in_array($fieldInfo->getType(), [FieldDescription::FIELD_TYPE_FILE])) {
            if (
                (E()->Document->getRights() > ACCESS_READ)
                &&
                ($fieldInfo->getMode() > ACCESS_READ)
            ) {
                E()->Document->addTranslation('TXT_CLEAR');
                E()->Document->addTranslation('BTN_QUICK_UPLOAD');

                $quick_upload_path = $this->getConfigValue('repositories.quick_upload_path', 'uploads/public');
                $quick_upload_pid = $this->dbh->getScalar('SELECT upl_id FROM share_uploads WHERE upl_path=%s LIMIT 1', $quick_upload_path);
                $quick_upload_enabled = true;

                if ($quick_upload_pid) {
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
                        $playlist = [];
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
        } elseif (($fieldInfo->getType() == FieldDescription::FIELD_TYPE_SMAP_SELECTOR) && $fieldValue) {
            $result->setAttribute('smap_name', E()->SiteManager->getSiteByPage($fieldValue)->name . ' : ' . $this->dbh->getScalar('share_sitemap_translation', 'smap_name', ['smap_id' => $fieldValue, 'lang_id' => E()->Language->getCurrent()]));
        } elseif ($fieldInfo->getType() == FieldDescription::FIELD_TYPE_CAPTCHA) {
            $fieldValue = $this->getConfigValue('recaptcha.public');
        } elseif ($fieldInfo->getType() == FieldDescription::FIELD_TYPE_LOOKUP && $fieldValue) {
            $value = $this->document->createElement('value', $fieldValue['value']);
            $value->setAttribute('id', $fieldValue['id']);
            $fieldValue = $value;
        }
        foreach ($fieldInfo as $propName => $propValue) {
            if ($propValue && is_scalar($propValue) && ($propValue !== '0')) {
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
                $result->appendChild($this->document->importNode($fieldValue, true));
            }
        } elseif ($fieldInfo->getType() ==
            FieldDescription::FIELD_TYPE_TEXTBOX_LIST
        ) {
            if ($fieldValue = $this->createTextBoxItems($fieldValue)) {
                try {
                    $result->appendChild($fieldValue);
                } catch (\Exception $e) {
                    $result->appendChild($this->document->importNode($fieldValue, true));
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
        } elseif ($fieldValue !== false) {
            // empty() не пропускает значиния 0 и '0'
            if (!empty($fieldValue) || ($fieldValue === 0) || ($fieldValue === '0')) {
                switch ($fieldInfo->getType()) {
                    case FieldDescription::FIELD_TYPE_STRING:
                        $fieldValue =E()->Utils->formatDate($fieldValue, $fieldInfo->getPropertyValue('outputFormat'), $fieldInfo->getType());
                        break;
                    case FieldDescription::FIELD_TYPE_DATETIME:
                    case FieldDescription::FIELD_TYPE_DATE:
                    case FieldDescription::FIELD_TYPE_TIME:
                        $result->setAttribute('date', $fieldValue);
                        $fieldValue =E()->Utils->formatDate($fieldValue, $fieldInfo->getPropertyValue('outputFormat'), $fieldInfo->getType());

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
            ['%2F', '+'],
            ['/', '%20'],
            urlencode($url)
        );
    }


    /**
     * Create the set of possible field values with type 'select'.
     *
     * @param FieldDescription $fieldInfo Field description.
     * @param mixed $data Data.
     * @return DOMNode
     */
    protected function createOptions(FieldDescription $fieldInfo, $data = false) {
        $fieldValue = $this->document->createElement('options');
        if (is_array($fieldInfo->getAvailableValues()))
            foreach ($fieldInfo->getAvailableValues() as $key => $option) {
                $dom_option =
                    $this->document->createElement('option', str_replace('&', '&amp;', $option['value']));
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
    protected function createTextBoxItems($data = []) {
        $fieldValue = false;

        if ($data === false) {
            return false;
        } elseif (!is_array($data)) {
            $data = [$data];
        }

        if (!empty($data)) {
            $fieldValue = $this->document->createElement('items');
            foreach ($data as $itemId => $itemData) {
                $item = $this->document->createElement('item', (string)$itemData);
                $item->setAttribute('id', $itemId);
                $fieldValue->appendChild($item);
            }
        }
        return $fieldValue;
    }
}
