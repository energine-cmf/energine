<?php
/**
 * @file
 * FieldDescription.
 *
 * It contains the definition to:
 * @code
class FieldDescription;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;

/**
 * Description of the field data.
 *
 * @code
class FieldDescription;
@endcode
 */
class FieldDescription extends DBWorker implements \Iterator {
    /**
     * Additional property names.
     * It is initialized in FieldDescription::$rewind and used by iterations.@n
     * This variable was created for eliminating calling 'array_keys' by iterations.
     *
     * @var array $additionalPropertiesNames
     */
    private $additionalPropertiesNames;
    /**
     * Additional property names in the lowercase.
     * @var array $additionalPropertiesLower
     * @todo Это на самом деле костыль
     */
    private $additionalPropertiesLower;
    /**
     * Current additional property ID.
     * For iteration over FieldDescription::$additionalProperties
     * @var int $propertiesIndex
     */
    private $propertiesIndex;
    /**
     * Default field name.
     * @var string EMPTY_FIELD_NAME
     */
    const EMPTY_FIELD_NAME = 'DUMMY';

    // Визуальные типы полей:
    /**
     * Visual field type for string.
     * @var string FIELD_TYPE_STRING
     */
    const FIELD_TYPE_STRING = 'string';

    /**
     * Visual field type for text.
     * @var string FIELD_TYPE_TEXT
     */
    const FIELD_TYPE_TEXT = 'text';
    /**
    Visual field type for code.
     * @var string FIELD_TYPE_CODE
     */
    const FIELD_TYPE_CODE = 'code';

    /**
     * Visual field type for password.
     * @var string FIELD_TYPE_PWD
     */
    const FIELD_TYPE_PWD = 'password';

    /**
     * Visual field type for E-mail.
     * @var string FIELD_TYPE_EMAIL
     */
    const FIELD_TYPE_EMAIL = 'email';
    /**
     * Visual field type for captcha.
     * @var string FIELD_TYPE_CAPTCHA
     */
    const FIELD_TYPE_CAPTCHA = 'captcha';

    /**
     * Visual field type for phone.
     * @var string FIELD_TYPE_PHONE
     */
    const FIELD_TYPE_PHONE = 'phone';

    /**
     * Visual field type for integer.
     * @var string FIELD_TYPE_INT
     */
    const FIELD_TYPE_INT = 'integer';

    /**
     * Visual field type for float.
     * @var string FIELD_TYPE_FLOAT
     */
    const FIELD_TYPE_FLOAT = 'float';

    /*
     * Visual field type for image.
     * @var string FIELD_TYPE_IMAGE
     */
    //const FIELD_TYPE_IMAGE = 'image';

    /**
     * Visual field type for file.
     * @var string FIELD_TYPE_FILE
     */
    const FIELD_TYPE_FILE = 'file';

    /*
     * Visual field type for private file.
     * @var string FIELD_TYPE_PFILE
     */
    //const FIELD_TYPE_PFILE = 'pfile';
    /**
     * Visual field type for thumbnail.
     * @var string FIELD_TYPE_THUMB
     */
    const FIELD_TYPE_THUMB = 'thumb';

    /**
     * Visual field type for boolean.
     * @var string FIELD_TYPE_BOOL
     */
    const FIELD_TYPE_BOOL = 'boolean';

    /**
     * Visual field type for HTML block.
     * @var string FIELD_TYPE_HTML_BLOCK
     */
    const FIELD_TYPE_HTML_BLOCK = 'htmlblock';

    /**
     * Visual field type for single selection.
     * @var string FIELD_TYPE_SELECT
     */
    const FIELD_TYPE_SELECT = 'select';

    /**
     * Visual field type for multiple selections.
     * @var string FIELD_TYPE_MULTI
     */
    const FIELD_TYPE_MULTI = 'multi';

    /**
     * Visual field type for select value.
     * @var string FIELD_TYPE_VALUE
     */
    const FIELD_TYPE_VALUE = 'value';

    /**
     * Visual field type for date and time.
     * @var string FIELD_TYPE_DATETIME
     */
    const FIELD_TYPE_DATETIME = 'datetime';

    /**
     * Visual field type for date.
     * @var string FIELD_TYPE_DATE
     */
    const FIELD_TYPE_DATE = 'date';

    /**
     * Visual field type for time.
     * @var string FIELD_TYPE_TIME
     */
    const FIELD_TYPE_TIME = 'time';

    /**
     * Visual field type for hidden field.
     * @var string FIELD_TYPE_HIDDEN
     */
    const FIELD_TYPE_HIDDEN = 'hidden';
    /**
     * Visual field type for additional info field.
     * @var string FIELD_TYPE_INFO
     */
    const FIELD_TYPE_INFO = 'info';


    /**
     * Visual field type for custom field type.
     * It can contain any data.
     * @var string FIELD_TYPE_CUSTOM
     */
    const FIELD_TYPE_CUSTOM = 'custom';

    /**
     * Visual field type for tab in form.
     * The content of the tab will be loaded by the value from this field.
     * @var string FIELD_TYPE_TAB
     */
    const FIELD_TYPE_TAB = 'tab';
    /**
     * Visual field type for video data.
     * In flv format. If the 'ffmpeg' is set that it will be converted from one of the supported formats.
     * @var string FIELD_TYPE_VIDEO
     */
    const FIELD_TYPE_VIDEO = 'video';
    /**
     * Visual field type for media data.
     * @var string FIELD_TYPE_MEDIA
     */
    const FIELD_TYPE_MEDIA = 'media';
    /**
     * Visual field type for textbox list.
     * Used for tags.
     * @var string FIELD_TYPE_TEXTBOX_LIST
     */
    const FIELD_TYPE_TEXTBOX_LIST = 'textbox';
    /**
     * Visual field type for section selecting.
     * Forwarding through /selector/ shall be provided.
     * @var string FIELD_TYPE_SMAP_SELECTOR
     */
    const FIELD_TYPE_SMAP_SELECTOR = 'smap';

    // Режимы отображения полей:
    /**
     * Field mode: NONE.
     * @var int FIELD_MODE_NONE
     */
    const FIELD_MODE_NONE = 0;
    /**
     * Field mode: READ.
     * @var int FIELD_MODE_READ
     */
    const FIELD_MODE_READ = 1;
    /**
     * Field mode: EDIT.
     * @var int FIELD_MODE_EDIT
     */
    const FIELD_MODE_EDIT = 2;
    /**
     * Field mode: FULL CONTROL.
     * @var int FIELD_MODE_FC
     */
    const FIELD_MODE_FC = 3;

    /**
     * Set of all available values for 'select' fields.
     * @var array $availableValues
     */
    private $availableValues;

    /**
     * Field name.
     * @var string $name
     */
    private $name;

    /**
     * System field name.
     * For fields from data base it includes table name: @code tableName[field_name] @endcode
     *
     * @var string $systemName
     */
    private $systemName;

    /**
     * Visual type.
     * @var string $type
     */
    private $type;

    /**
     * Field type in data base.
     * @var string $systemType
     */
    private $systemType;

    /**
     * Field mode.
     * @var int $mode
     */
    private $mode = self::FIELD_MODE_EDIT;

    /**
     * Right level for the field.
     * @var int $rights
     */
    private $rights;

    /**
     * Defines whether the data in the field is multilingual.
     * @var boolean $isMultilanguage
     */
    private $isMultilanguage;

    /**
     * Additional field properties.
     * It is a hash like @code array(propertyName => propertyValue)@endcode
     * Iteration works with this variable.
     *
     * @var Object $additionalProperties
     */
    private $additionalProperties;

    //todo VZ: Why not to set this to 1 or 0?
    //todo Pavka: Really why?
    /**
     * Field length.
     * For the fields that has not the length it will set to @c true.
     *
     * @var int $length
     */
    private $length = true;

    /**
     * @param string $name Field name.
     */
    public function __construct($name = self::EMPTY_FIELD_NAME) {
        parent::__construct();

        $this->name = $name;
        $this->systemName = $name;
        $this->isMultilanguage = false;
        $this->additionalProperties = $this->additionalPropertiesLower = array();

        // формируем название поля добавляя префикс 'FIELD_'
        if ($name != self::EMPTY_FIELD_NAME) {
            $this->setProperty('title', 'FIELD_' . $name);
            //$this->setProperty('title', $this->translate('FIELD_'.$name));
        }

    }

    //todo VZ: This always return true. Why?
    /**
     * Load field description form the array.
     *
     * @param array $fieldInfo Field information.
     * @return boolean
     */
    public function loadArray(array $fieldInfo) {
        $result = true;
        foreach ($fieldInfo as $propName => $propValue) {
            switch ($propName) {
                case 'type':
                    $this->setSystemType($propValue);
                    break;
                case 'length':
                    $this->setLength($propValue);
                    break;
                case 'mode':
                    $this->setMode($propValue);
                    break;
                case 'isMultilanguage':
                    $this->isMultilanguage = true;
                    break;
                default:
                    $this->setProperty($propName, $propValue);
            }
        }
        if (isset($fieldInfo['index']) && ($fieldInfo['index'] == 'PRI')) {
            $this->setType(FieldDescription::FIELD_TYPE_HIDDEN);
        }
        return $result;
    }

    //todo VZ: This always return true. Why?
    /**
     * Load field description from XML-description.
     *
     * @param \SimpleXMLElement $fieldInfo XML element.
     * @return boolean
     */
    public function loadXML(\SimpleXMLElement $fieldInfo) {
        $result = true;
        foreach ($fieldInfo->attributes() as $attrName => $attrValue) {
            $attrName = (string)$attrName;
            $attrValue = (string)$attrValue;
            switch ($attrName) {
                case 'name':
                    $this->name = $attrValue;
                    break;
                case 'type':
                    $this->setSystemType($attrValue);
                    if (($this->getType() == self::FIELD_TYPE_SELECT)
                        && !empty($fieldInfo->options->option)
                    ) {
                        $this->loadAvailableXMLValues($fieldInfo->options->option);
                    }
                    break;
                case 'length':
                    $this->setLength($attrValue);
                    break;
                case 'mode':
                    $this->setMode($attrValue);
                    break;
                default:
                    /*if(in_array($attrName, array('title', 'message', 'tabName'))){
                             $attrValue = $this->translate($attrValue);
                         }*/
                    $this->setProperty($attrName, $attrValue);
            }
        }
        return $result;
    }

    /**
     * Get field name.
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set system field name.
     *
     * @param string $systemName System name.
     */
    public function setSystemName($systemName) {
        $this->systemName = $systemName;
    }

    /**
     * Get field length.
     *
     * @return int|true
     */
    public function getLength() {
        return $this->length;
    }

    /**
     * Set field length.
     *
     * @param int $length Length.
     * @return FieldDescription
     */
    public function setLength($length) {
        $this->length = (int)$length;
        return $this;
    }

    /**
     * Get system name.
     *
     * @return string
     */
    public function getSystemName() {
        return $this->systemName;
    }

    /**
     * Set visual type.
     *
     * @param string $type Visual field type.
     * @return FieldDescription
     */
    public function setType($type) {
        $this->type = (string)$type;
        $this->setProperty('sort', 0);
        switch ($this->type) {
            case self::FIELD_TYPE_PWD :
                $this->setProperty('pattern', '/^.+$/');
                $this->setProperty('message', 'MSG_FIELD_IS_NOT_NULL' /*$this->translate('MSG_FIELD_IS_NOT_NULL')*/);
                //$this->setProperty('outputFormat', '%s');
                break;
            case self::FIELD_TYPE_HIDDEN :
                /*if (is_null($this->getPropertyValue('outputFormat'))) {
                	$this->setProperty('outputFormat', '%s');
                }*/
                break;
            case self::FIELD_TYPE_EMAIL:
                if (($this->getPropertyValue('nullable') === false) || is_null($this->getPropertyValue('nullable'))) {
                    $regexp = '/^(([^()<>@,;:\\\".\[\] ]+)|("[^"\\\\\r]*"))((\.[^()<>@,;:\\\".\[\] ]+)|(\."[^"\\\\\r]*"))*@(([a-z0-9][a-z0-9\-]+)*[a-z0-9]+\.)+[a-z]{2,}$/i';
                } else {
                    $regexp = '/^((([^()<>@,;:\\\".\[\] ]+)|("[^"\\\\\r]*"))((\.[^()<>@,;:\\\".\[\] ]+)|(\."[^"\\\\\r]*"))*@(([a-z0-9][a-z0-9\-]+)*[a-z0-9]+\.)+[a-z]{2,})?$/i';
                }
                $this->setProperty('pattern', $regexp);
                $this->setProperty('sort', 1);
                //$this->setProperty('message', $this->translate('MSG_BAD_EMAIL_FORMAT'));
                $this->setProperty('message', 'MSG_BAD_EMAIL_FORMAT');
                //$this->setProperty('outputFormat', '%s');
                break;
            case  self::FIELD_TYPE_PHONE:
                if ($this->getPropertyValue('nullable') === false || is_null($this->getPropertyValue('nullable'))) {
                    $regexp = '/^[0-9\(\)\+\-\. ]{5,25}$/';
                } else {
                    $regexp = '/^([0-9\(\)\+\-\. ]{5,25})?$/';
                }
                $this->setProperty('sort', 1);
                $this->setProperty('pattern', $regexp);
                //$this->setProperty('message', $this->translate('MSG_BAD_PHONE_FORMAT'));
                $this->setProperty('message', 'MSG_BAD_PHONE_FORMAT');
                //$this->setProperty('outputFormat', '%s');
                break;
            /*case self::FIELD_TYPE_IMAGE:
                if ($this->getPropertyValue('nullable') === false) {
                    $this->setProperty('pattern', '/^.+$/');
                    //$this->setProperty('message', $this->translate('MSG_IMG_IS_NOT_NULL'));
                    $this->setProperty('message', 'MSG_IMG_IS_NOT_NULL');
                }
                $this->length = true;
                //$this->setProperty('outputFormat', '%s');
                $this->setProperty('deleteFileTitle', $this->translate('MSG_DELETE_FILE'));
                break;*/
            case self::FIELD_TYPE_FILE:
                //case self::FIELD_TYPE_PFILE:
            case self::FIELD_TYPE_VIDEO:
                if ($this->getPropertyValue('nullable') === false) {
                    $this->setProperty('pattern', '/^.+$/');
                    //$this->setProperty('message', $this->translate('MSG_FILE_IS_NOT_NULL'));
                    $this->setProperty('message', 'MSG_FILE_IS_NOT_NULL');
                }
                $this->length = true;
                //$this->setProperty('outputFormat', '%s');
                //$this->setProperty('deleteFileTitle', $this->translate('MSG_DELETE_FILE'));
                break;
            case self::FIELD_TYPE_STRING:
                $this->setProperty('sort', 1);
                if ($this->getPropertyValue('nullable') === false || is_null($this->getPropertyValue('nullable'))) {
                    $this->setProperty('pattern', '/^.+$/');
                    //$this->setProperty('message', $this->translate('MSG_FIELD_IS_NOT_NULL'));
                    $this->setProperty('message', 'MSG_FIELD_IS_NOT_NULL');
                } else {
                    //Убирается на случай если мы приводим к этому типу
                    //по хорошему такое нужно для всех типов делать
                    $this->removeProperty('pattern');
                    $this->removeProperty('message');
                }
                //$this->setProperty('outputFormat', '%s');
                break;
            case self::FIELD_TYPE_FLOAT:
                $this->length = 10;
                if (
                    ($this->getPropertyValue('nullable') === false)
                    ||
                    (is_null($this->getPropertyValue('nullable')))
                ) {
                    $regexp = '/^[0-9,\.]{1,' . $this->length . '}$/';
                } else {
                    $regexp = '/^[0-9,\.]{0,' . $this->length . '}$/';
                }
                $this->setProperty('sort', 1);
                //$this->setProperty('outputFormat', '%f');
                $this->setProperty('pattern', $regexp);
                //$this->setProperty('message', $this->translate('MSG_BAD_FLOAT_FORMAT'));
                $this->setProperty('message', 'MSG_BAD_FLOAT_FORMAT');
                break;
            case self::FIELD_TYPE_BOOL:
                $this->length = true;
                $this->setProperty('outputFormat', '%s');
                $this->setProperty('sort', 1);
                break;
            case self::FIELD_TYPE_CAPTCHA:
                $this->setProperty('customField', 'customField');
                break;
            case self::FIELD_TYPE_SELECT:
                $this->length = true;
                break;
            case self::FIELD_TYPE_INT:
                if (!$this->getPropertyValue('key')) {
                    if ($this->getPropertyValue('nullable') === false) {
                        $regexp = '/^\d{1,7}$/';
                        //$message = $this->translate('MSG_BAD_INT_FORMAT_OR_NULL');
                        $message = 'MSG_BAD_INT_FORMAT_OR_NULL';
                    } else {
                        $regexp = '/^\d{0,7}$/';
                        //$message = $this->translate('MSG_BAD_INT_FORMAT');
                        $message = 'MSG_BAD_INT_FORMAT';
                    }
                    $this->setProperty('sort', 1);
                    $this->setProperty('pattern', $regexp);
                    $this->setProperty('message', $message);
                    //$this->setProperty('outputFormat', '%d');
                }
                break;
            case self::FIELD_TYPE_TEXT:
            case self::FIELD_TYPE_HTML_BLOCK:
            case self::FIELD_TYPE_CODE:
                if ($this->getPropertyValue('nullable') === false || is_null($this->getPropertyValue('nullable'))) {
                    $this->setProperty('pattern', '/^.+$/m');
                    //$this->setProperty('message', $this->translate('MSG_FIELD_IS_NOT_NULL'));
                    $this->setProperty('message', 'MSG_FIELD_IS_NOT_NULL');
                }
                //$this->setProperty('outputFormat', '%s');
                $this->length = true;
                break;
            case self::FIELD_TYPE_DATETIME:
                if ($this->getPropertyValue('nullable') === false) {
                    //$regexp = '/^\d{4}\-\d{1,2}\-\d{1,2} \d{1,2}:\d{1,2}:\d{1,2}$/';
                    $regexp = '/^\d{4}\-\d{1,2}\-\d{1,2} \d{1,2}:\d{1,2}(:\d{1,2})?$/';
                } else {
                    $regexp = '/^(\d{4}\-\d{1,2}\-\d{1,2} \d{1,2}:\d{1,2}(:\d{1,2})?)?$/';
                }
                $this->setProperty('sort', 1);
                $this->setProperty('pattern', $regexp);
                $this->setProperty('outputFormat', '%Y-%m-%d %H:%M');
                //$this->setProperty('message', $this->translate('MSG_WRONG_DATETIME_FORMAT'));
                $this->setProperty('message', 'MSG_WRONG_DATETIME_FORMAT');
                $this->length = true;
                break;
            case self::FIELD_TYPE_TIME:
                if ($this->getPropertyValue('nullable') === false) {
                    $regexp = '/^\d{1,2}:\d{1,2}(:\d{1,2})?$/';
                } else {
                    $regexp = '/^\d{1,2}:\d{1,2}(:\d{1,2})?$/';
                }
                $this->setProperty('pattern', $regexp);
                //$this->setProperty('message', $this->translate('MSG_WRONG_TIME_FORMAT'));
                $this->setProperty('message', 'MSG_WRONG_TIME_FORMAT');
                $this->setProperty('sort', 1);
                $this->setProperty('outputFormat', '%H:%M:%S');
                $this->length = true;
                break;
            case self::FIELD_TYPE_DATE:
                if ($this->getPropertyValue('nullable') === false) {
                    $regexp = '/^\d{4}\-\d{1,2}\-\d{1,2}$/';
                } else {
                    $regexp = '/^(\d{4}\-\d{1,2}\-\d{1,2})?$/';
                }
                $this->setProperty('sort', 1);
                $this->setProperty('pattern', $regexp);
                $this->setProperty('outputFormat', '%Y-%m-%d');
                //$this->setProperty('message', $this->translate('MSG_WRONG_DATE_FORMAT'));
                $this->setProperty('message', 'MSG_WRONG_DATE_FORMAT');
                $this->length = true;
                break;
            case self::FIELD_TYPE_TEXTBOX_LIST:

                break;
            case self::FIELD_TYPE_CUSTOM:
                if ($this->getPropertyValue('nullable') === false) {
                    $this->setProperty('pattern', '/^.+$/');
                    //$this->setProperty('message', $this->translate('MSG_FIELD_IS_NOT_NULL'));
                    $this->setProperty('message', 'MSG_FIELD_IS_NOT_NULL');
                }
                break;
            default:
                break;
        }

        return $this;
    }

    /**
     * Get visual type.
     *
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Set system field type.
     * At the same time the visual field type will be set based on system type.
     *
     * @param string $systemType
     */
    public function setSystemType($systemType) {
        $this->systemType = $systemType;
        $this->setType(self::convertType($systemType, $this->name, $this->length, $this->additionalProperties));
    }

    /**
     * Get system type.
     *
     * @return string
     */
    public function getSystemType() {
        return $this->systemType;
    }

    /**
     * Set Field mode.
     *
     * @param int $mode Mode.
     * @return FieldDescription
     */
    public function setMode($mode) {
        $this->mode = $mode;
        return $this;
    }

    /**
     * Get field mode.
     *
     * @return int
     */
    public function getMode() {
        return $this->mode;
    }

    /**
     * Set field rights.
     *
     * @param int $rights Rights.
     */
    public function setRights($rights) {
        $this->rights = $rights;
    }

    /**
     * Get rights.
     *
     * @return int
     */
    public function getRights() {
        return $this->rights;
    }

    /**
     * Set field property.
     * Some properties an values, that contain reserved construction @c trans(), will be automatic translated.
     *
     * @param string $name Property name.
     * @param mixed $value Property value.
     * @return FieldDescription
     */
    public function setProperty($name, $value) {
        if (in_array($name, array('title', 'message', 'tabName'))) {
            $value = $this->translate($value);
        } elseif (is_scalar($value) && (strpos($value, 'trans(') !== false)) {
            $value = $this->translate(str_replace(array('trans', '(', ')'), '', $value));
        }
        $this->additionalProperties[$name] = $this->additionalPropertiesLower[strtolower($name)] = $value;
        return $this;
    }

    /**
     * Remove property.
     *
     * @param string $name Property name.
     * @return FieldDescription
     */
    public function removeProperty($name) {
        unset($this->additionalProperties[$name], $this->additionalPropertiesLower[strtolower($name)]);
        return $this;
    }

    /**
     * Get the list of additional property names.
     *
     * @return array
     */
    public function getPropertyNames() {
        return array_keys($this->additionalProperties);
    }

    /**
     * Get property value.
     *
     * @param string $name Property name.
     * @return mixed
     */
    public function getPropertyValue($name) {
        $value = null;
        if (isset($this->additionalPropertiesLower[strtolower($name)])) {
            $value = $this->additionalPropertiesLower[strtolower($name)];
        } elseif (isset($this->additionalProperties[$name])) {
            $value = $this->additionalProperties[$name];
        }
        return $value;
    }

    /**
     * Convert field type from system type to visual type.
     *
     * @param string $systemType System type.
     * @param string $name Field name.
     * @param int $length Field length.
     * @param array $props Field properties.
     * @return string
     */
    static public function convertType($systemType, $name, $length = 1, $props = array()) {
        switch ($systemType) {
            case DBA::COLTYPE_STRING:
                if (strpos($name, '_password')) {
                    $result = self::FIELD_TYPE_PWD;
                } elseif (strpos($name, '_email')) {
                    $result = self::FIELD_TYPE_EMAIL;
                } elseif (strpos($name, '_phone')) {
                    $result = self::FIELD_TYPE_PHONE;
                } /*elseif (strpos($name, '_img')) {
                    $result = self::FIELD_TYPE_IMAGE;
                }*/
                elseif (strpos($name, '_file') || strpos($name, '_img')) {
                    $result = self::FIELD_TYPE_FILE;
                } /*elseif (strpos($name, '_pfile')) {
                    $result = self::FIELD_TYPE_PFILE;
                }*/
                elseif (strpos($name, '_video')) {
                    $result = self::FIELD_TYPE_VIDEO;
                } else {
                    $result = self::FIELD_TYPE_STRING;
                }
                break;
            case DBA::COLTYPE_FLOAT:
                $result = self::FIELD_TYPE_FLOAT;
                break;
            case DBA::COLTYPE_INTEGER:
                if ($length == 1) {
                    if (strpos($name, '_info')) {
                        $result = self::FIELD_TYPE_INFO;
                    } else {
                        $result = self::FIELD_TYPE_BOOL;
                    }
                } // обрабатываем внешний ключ
                elseif (isset($props['key']) && is_array($props['key'])) {
                    $result = (strpos($name, '_multi')) ? self::FIELD_TYPE_MULTI : self::FIELD_TYPE_SELECT;
                } else {
                    $result = self::FIELD_TYPE_INT;
                }
                break;
            case DBA::COLTYPE_TEXT:
                if (strpos($name, '_rtf')) {
                    $result = self::FIELD_TYPE_HTML_BLOCK;
                } elseif (strpos($name, '_code')) {
                    $result = self::FIELD_TYPE_CODE;
                } else {
                    $result = self::FIELD_TYPE_TEXT;
                }
                break;
            case DBA::COLTYPE_DATETIME:
            case DBA::COLTYPE_TIMESTAMP:
                $result = self::FIELD_TYPE_DATETIME;
                break;
            case DBA::COLTYPE_TIME:
                $result = self::FIELD_TYPE_TIME;
                break;
            case DBA::COLTYPE_DATE:
                $result = self::FIELD_TYPE_DATE;
                break;
            default:
                $result = $systemType;
        }
        return $result;
    }

    /**
     * Intersect the configuration meta-data with meta-data from data base.
     *
     * @param FieldDescription $configFieldDescription Configuration meta-data.
     * @param FieldDescription $dbFieldDescription Meta-data from data base.
     * @return FieldDescription
     */
    public static function intersect(FieldDescription $configFieldDescription, FieldDescription $dbFieldDescription) {
        $type = $configFieldDescription->getType();
        $mode = $configFieldDescription->getMode();
        if (!is_null($av = $configFieldDescription->getAvailableValues())) {
            $dbFieldDescription->setAvailableValues($av);
        }
        if ($dbFieldDescription->getPropertyValue('index') == 'PRI') {
            $dbFieldDescription->setType(FieldDescription::FIELD_TYPE_HIDDEN);
        }
        if (!is_null($type)) {
            $dbFieldDescription->setProperty('origType', $dbFieldDescription->getType());
            //меняем тип
            $dbFieldDescription->setType($type);
        }
        if (!is_null($mode)) {
            $dbFieldDescription->setMode($mode);
        }
        $dbFieldDescription->isMultilanguage = $configFieldDescription->isMultilanguage || $dbFieldDescription->isMultilanguage();
        $properties = array_unique(array_merge($configFieldDescription->getPropertyNames(), $dbFieldDescription->getPropertyNames()));

        foreach ($properties as $propertyName) {
            $propertyValue = $configFieldDescription->getPropertyValue($propertyName);

            if (!is_null($propertyValue) && !($propertyName == 'title' && $propertyValue == 'FIELD_' . self::EMPTY_FIELD_NAME)) {
                $dbFieldDescription->setProperty($propertyName, $propertyValue);
            }
        }
        return $dbFieldDescription;
    }

    /**
     * Validate data.
     *
     * @param mixed $data Data.
     * @return boolean
     */
    public function validate($data) {
        if (is_int($this->length) && strlen($data) > $this->length) {
            return false;
        }
        if ($this->getPropertyValue('pattern') && !preg_match($this->getPropertyValue('pattern'), $data)) {
            return false;
        }
        return true;
    }

    /**
     * Check if the data in the field is multilingual.
     *
     * @return boolean
     */
    public function isMultilanguage() {
        return $this->isMultilanguage;
    }

    /**
     * Mark the field as multilingual.
     */
    public function markMultilanguage() {
        $this->isMultilanguage = true;
    }

    /**
     * Load the set of available field values.
     *
     * @param mixed $values Set of values.
     * @param string $keyName Name of the key field.
     * @param string $valueName Field name of the main value.
     * @return $this
     *
     * @see QAL::select()
     */
    public function loadAvailableValues($values, $keyName, $valueName) {
        if (is_array($values) && empty($this->availableValues)) {
            $result = array();
            foreach ($values as $row) {
                $key = $row[$keyName];
                $value = $row[$valueName];

                unset($row[$keyName]);
                unset($row[$valueName]);

                $result[$key] = array(
                    'value' => $value,
                    'attributes' => (empty($row) ? false : $row)
                );
            }
            $this->availableValues = $result;
        }
        return $this;
    }

    /**
     * Load available values from the component config.
     * @code
<field type="select">
    <options>
        <option id="1" [attributes]>TXT_1</option>
        <option id="2" [attributes]>TXT_2</option>
    </options>
</field>
@endcode
     *
     * @param \SimpleXMLElement $options Options.
     */
    private function loadAvailableXMLValues(\SimpleXMLElement $options) {
        $result = array();
        foreach ($options as $option) {
            $optAttributes = array();
            foreach ($option->attributes() as $optAttrName => $optAttrValue) {
                if ((string)$optAttrName != 'id') {
                    $optAttributes[(string)$optAttrName] = (string)$optAttrValue;
                }
            }
            $result[(int)$option['id']] = array(
                'value' => $this->translate((string)$option),
                'attributes' => $optAttributes
            );
        }
        $this->availableValues = $result;
    }

    /**
     * Get available field values.
     *
     * @return array
     */
    public function &getAvailableValues() {
        return $this->availableValues;
    }

    /**
     * Set available values.
     *
     * @param array $av Available values.
     * @return FieldDescription
     */
    public function setAvailableValues($av) {
        $this->availableValues = $av;
        return $this;
    }

    //todo VZ: What is $RORights and $FCRights?
    /**
     * Compute the field rights.
     *
     * @param int $methodRights Method rights.
     * @param null|int $RORights
     * @param null|int $FCRights
     * @return int
     */
    public static function computeRights($methodRights, $RORights = null, $FCRights = null) {
        //Если уровень прав не указан, берем права документа
        $RORights = is_null($RORights) ? $methodRights : $RORights;
        $FCRights = is_null($FCRights) ? $methodRights : $FCRights;


        //Если права на чтение на контрол меньше чем права на метод, то контрол - невидим
        if ($methodRights < $RORights) {
            $result = FieldDescription::FIELD_MODE_NONE;
        } //Если права на чтение на контрол больше или равны правам на метод, и права на запись меньше  - просто выводится текст контрола
        elseif ($methodRights >= $RORights && $methodRights < $FCRights) {
            $result = FieldDescription::FIELD_MODE_READ;
        } elseif ($methodRights >= $FCRights) {
            $result = FieldDescription::FIELD_MODE_EDIT;
        }

        return $result;
    }

    public function current() {
        return $this->additionalProperties[$this->additionalPropertiesNames[$this->propertiesIndex]];
    }

    public function key() {
        return $this->additionalPropertiesNames[$this->propertiesIndex];
    }

    public function next() {
        $this->propertiesIndex++;
    }

    public function rewind() {
        $this->additionalPropertiesNames = array_keys($this->additionalProperties);
        $this->propertiesIndex = 0;
    }

    public function valid() {
        return isset($this->additionalPropertiesNames[$this->propertiesIndex]);
    }
}
