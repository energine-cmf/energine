<?php

/**
 * Класс FieldDescription.
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @copyright ColoCall 2006
 * @version $Id$
 */

//require_once('core/framework/DBWorker.class.php');

/**
 * Описание поля данных.
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 */
class FieldDescription extends DBWorker {
    /**
     * Имя поля для которого не указано имя :)
     *
     */
    const EMPTY_FIELD_NAME = 'DUMMY';

    /*
    * Визуальные типы полей:
    */

    /**
     * Строка
     */
    const FIELD_TYPE_STRING = 'string';

    /**
     * Текст
     */
    const FIELD_TYPE_TEXT = 'text';

    /**
     * Пароль
     */
    const FIELD_TYPE_PWD = 'password';

    /**
     * E-mail
     */
    const FIELD_TYPE_EMAIL = 'email';

    /**
     * Телефонный номер
     */
    const FIELD_TYPE_PHONE = 'phone';

    /**
     * Целое число
     */
    const FIELD_TYPE_INT = 'integer';

    /**
     * Число с плавающей точкой
     */
    const FIELD_TYPE_FLOAT = 'float';

    /**
     * Изображение
     */
    const FIELD_TYPE_IMAGE = 'image';

    /**
     * Файл
     */
    const FIELD_TYPE_FILE = 'file';

    /**
     * Приватный файл
     */
    const FIELD_TYPE_PFILE = 'pfile';

    /**
     * Защищенный файл
     */
    const FIELD_TYPE_PRFILE = 'prfile';

    /**
     * Булево значение
     */
    const FIELD_TYPE_BOOL = 'boolean';

    /**
     * HTML блок
     */
    const FIELD_TYPE_HTML_BLOCK = 'htmlblock';

    /**
     * Единичный выбор из нескольких вариантов
     */
    const FIELD_TYPE_SELECT = 'select';

    /**
     * Множественный выбор из нескольких вариантов
     */
    const FIELD_TYPE_MULTI = 'multi';

    /**
     * Дата и время
     */
    const FIELD_TYPE_DATETIME = 'datetime';

    /**
     * Дата
     */
    const FIELD_TYPE_DATE = 'date';

    /**
     * Скрытое поле
     */
    const FIELD_TYPE_HIDDEN = 'hidden';

    /**
     * Пользовательский тип поля (может содержать любые данные)
     *
     */
    const FIELD_TYPE_CUSTOM = 'custom';

    /*
    * Режимы отображения полей:
    */

    /**
     * Поле не отображается
     */
    const FIELD_MODE_NONE = 0;

    /**
     * Только для чтения
     */
    const FIELD_MODE_READ = 1;

    /**
     * Режим редактирования
     */
    const FIELD_MODE_EDIT = 2;

    /**
     * Полный контроль
     */
    const FIELD_MODE_FC = 3;

    /**
     * @access private
     * @var array набор возможных значений (для полей типа select)
     */
    private $availableValues;

    /**
     * @access private
     * @var string имя поля
     */
    private $name;

    /**
     * Для полей из БД, включает имя таблицы: tableName[name]
     *
     * @access private
     * @var string системное имя поля
     */
    private $systemName;

    /**
     * @access private
     * @var string визуальный тип поля
     */
    private $type;

    /**
     * @access private
     * @var string тип поля в БД
     */
    private $systemType;

    /**
     * @access private
     * @var int режим отображения поля
     */
    private $mode = self::FIELD_MODE_EDIT;

    /**
     * @access private
     * @var int уровень прав на данное поле
     */
    private $rights;

    /**
     * @access private
     * @var boolean данные в поле мультиязычные?
     */
    private $isMultilanguage;

    /**
     * Хэш вида array(propertyName => propertyValue).
     *
     * @access private
     * @var Object дополнительные свойства поля
     */
    private $additionalProperties;

    /**
     * Для полей не имеющих длины устанавливается в true.
     *
     * @access private
     * @var int длина поля
     */
    private $length = true;

    /**
     * Конструктор класса.
     *
     * @access public
     * @param string $name имя поля
     * @return void
     */
    public function __construct($name = self::EMPTY_FIELD_NAME) {
        parent::__construct();

        $this->name = $name;
        $this->systemName = $name;
        $this->isMultilanguage = false;
        $this->additionalProperties = array();

        // формируем название поля добавляя префикс 'FIELD_'
        if ($name!=self::EMPTY_FIELD_NAME ) {
            $this->addProperty('title', $this->translate('FIELD_'.$name));
        }

    }

    /**
     * Загружает описание поля из массива.
     *
     * @access public
     * @param array $fieldInfo
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
                case 'isMultilanguage':
                    $this->isMultilanguage = true;
                    break;
                default:
                    $this->addProperty($propName, $propValue);
            }
        }
        return $result;
    }

    /**
     * Загружает описание поля из XML-описания.
     *
     * @access public
     * @param SimpleXMLElement $fieldInfo
     * @return boolean
     */
    public function loadXML(SimpleXMLElement $fieldInfo) {
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
                    break;
                case 'length':
                    $this->setLength($attrValue);
                    break;
                case 'mode':
                    $this->setMode($attrValue);
                    break;
                default:
                    if ($attrName == 'title') {
                        $attrValue = $this->translate($attrValue);
                    }
                    elseif ($attrName == 'message') {
                        $attrValue = $this->translate($attrValue);
                    }
                    $this->addProperty($attrName, $attrValue);
            }
        }
        return $result;
    }

    /**
     * Возвращает имя поля.
     *
     * @access public
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Устанавливает системное имя поля.
     *
     * @access public
     * @param string $systemName
     * @return void
     */
    public function setSystemName($systemName) {
        $this->systemName = $systemName;
    }

    /**
     * Возвращает длину поля.
     *
     * @access public
     * @return int | true
     */
    public function getLength() {
        return $this->length;
    }

    /**
     * Устанавливает длину поля.
     *
     * @access public
     * @param int $length
     * @return void
     */
    public function setLength($length) {
        $this->length = (int)$length;
    }

    /**
     * Возвращает системное имя поля.
     *
     * @access public
     * @return string
     */
    public function getSystemName() {
        return $this->systemName;
    }

    /**
     * Устанавливает визуальный тип поля.
     *
     * @access public
     * @param string $type
     * @return void
     */
    public function setType($type) {
        $this->type = (string)$type;
        switch ($this->type) {
            case self::FIELD_TYPE_PWD :
                $this->addProperty('pattern', '/^.+$/');
                $this->addProperty('message', $this->translate('MSG_FIELD_IS_NOT_NULL'));
                $this->addProperty('outputFormat', '%s');
                break;
            case self::FIELD_TYPE_HIDDEN :
                if (is_null($this->getPropertyValue('outputFormat'))) {
                	$this->addProperty('outputFormat', '%s');
                }
                break;
            case self::FIELD_TYPE_EMAIL:
                if (($this->getPropertyValue('nullable') === false) || is_null($this->getPropertyValue('nullable'))) {
                    $regexp = '/^(([^()<>@,;:\\\".\[\] ]+)|("[^"\\\\\r]*"))((\.[^()<>@,;:\\\".\[\] ]+)|(\."[^"\\\\\r]*"))*@(([a-z0-9][a-z0-9\-]+)*[a-z0-9]+\.)+[a-z]{2,}$/i';
                }
                else {
                    $regexp = '/^((([^()<>@,;:\\\".\[\] ]+)|("[^"\\\\\r]*"))((\.[^()<>@,;:\\\".\[\] ]+)|(\."[^"\\\\\r]*"))*@(([a-z0-9][a-z0-9\-]+)*[a-z0-9]+\.)+[a-z]{2,})?$/i';
                }
                $this->addProperty('pattern', $regexp);
                $this->addProperty('message', $this->translate('MSG_BAD_EMAIL_FORMAT'));
                $this->addProperty('outputFormat', '%s');
                break;
            case  self::FIELD_TYPE_PHONE:
                if ($this->getPropertyValue('nullable') === false || is_null($this->getPropertyValue('nullable'))) {
                    $regexp = '/^[0-9\(\)\+\-\. ]{5,25}$/';
                }
                else {
                    $regexp = '/^([0-9\(\)\+\-\. ]{5,25})?$/';
                }
                $this->addProperty('pattern', $regexp);
                $this->addProperty('message', $this->translate('MSG_BAD_PHONE_FORMAT'));
                $this->addProperty('outputFormat', '%s');
                break;
            case self::FIELD_TYPE_IMAGE:
                if ($this->getPropertyValue('nullable') === false) {
                    $this->addProperty('pattern', '/^.+$/');
                    $this->addProperty('message', $this->translate('MSG_IMG_IS_NOT_NULL'));
                }
                $this->length = true;
                $this->addProperty('outputFormat', '%s');
                break;
            case self::FIELD_TYPE_FILE:
            case self::FIELD_TYPE_PRFILE:
            case self::FIELD_TYPE_PFILE:
                if ($this->getPropertyValue('nullable') === false) {
                    $this->addProperty('pattern', '/^.+$/');
                    $this->addProperty('message', $this->translate('MSG_FILE_IS_NOT_NULL'));
                }
                $this->length = true;
                $this->addProperty('outputFormat', '%s');
                break;
            case self::FIELD_TYPE_STRING:
                if ($this->getPropertyValue('nullable') === false  || is_null($this->getPropertyValue('nullable'))) {
                    $this->addProperty('pattern', '/^.+$/');
                    $this->addProperty('message', $this->translate('MSG_FIELD_IS_NOT_NULL'));
                }
                $this->addProperty('outputFormat', '%s');
                break;
            case self::FIELD_TYPE_FLOAT:
                $this->length = 10;
                if ($this->getPropertyValue('nullable') === false) {
                    $regexp = '/^[0-9,\.]{1,'.$this->length.'}$/';
                }
                else {
                    $regexp = '/^[0-9,\.]{0,'.$this->length.'}$/';
                }
                $this->addProperty('outputFormat', '%f');
                $this->addProperty('pattern', $regexp);
                $this->addProperty('message', $this->translate('MSG_BAD_FLOAT_FORMAT'));
                break;
            case self::FIELD_TYPE_BOOL:
                $this->length = true;
                $this->addProperty('outputFormat', '%s');
                break;
            case self::FIELD_TYPE_SELECT:
                $this->length = true;
                break;
            case self::FIELD_TYPE_INT:
                if (!$this->getPropertyValue('key')) {
                    if ($this->getPropertyValue('nullable') === false) {
                        $regexp = '/^\d{1,5}$/';
                        $message = $this->translate('MSG_BAD_INT_FORMAT_OR_NULL');
                    }
                    else {
                        $regexp = '/^\d{0,5}$/';
                        $message = $this->translate('MSG_BAD_INT_FORMAT');
                    }
                    $this->addProperty('pattern', $regexp);
                    $this->addProperty('message', $message);
                    $this->addProperty('outputFormat', '%d');
                }
                break;
            case self::FIELD_TYPE_TEXT:
            case self::FIELD_TYPE_HTML_BLOCK:
                if ($this->getPropertyValue('nullable') === false|| is_null($this->getPropertyValue('nullable'))) {
                    $this->addProperty('pattern', '/^.+$/m');
                    $this->addProperty('message', $this->translate('MSG_FIELD_IS_NOT_NULL'));
                }
                $this->addProperty('outputFormat', '%s');
                $this->length = true;
                break;
            case self::FIELD_TYPE_DATETIME:
                if ($this->getPropertyValue('nullable') === false) {
                    $regexp = '/^\d{4}\-\d{1,2}\-\d{1,2} \d{1,2}:\d{1,2}:\d{1,2}$/';
                }
                else {
                    $regexp = '/^(\d{4}\-\d{1,2}\-\d{1,2} \d{1,2}:\d{1,2}:\d{1,2})?$/';
                }
                $this->addProperty('pattern', $regexp);
                $this->addProperty('outputFormat', '%Y-%m-%d %H:%M:%S');
                $this->addProperty('message', $this->translate('MSG_WRONG_DATETIME_FORMAT'));
                $this->length = true;
                break;
            case self::FIELD_TYPE_DATE:
                if ($this->getPropertyValue('nullable') === false) {
                    $regexp = '/^\d{4}\-\d{1,2}\-\d{1,2}$/';
                }
                else {
                    $regexp = '/^(\d{4}\-\d{1,2}\-\d{1,2})?$/';
                }
                $this->addProperty('pattern', $regexp);
                $this->addProperty('outputFormat', '%Y-%m-%d');
                $this->addProperty('message', $this->translate('MSG_WRONG_DATE_FORMAT'));
                $this->length = true;
                break;
            case self::FIELD_TYPE_CUSTOM:
                if ($this->getPropertyValue('nullable') === false) {
                    $this->addProperty('pattern', '/^.+$/');
                    $this->addProperty('message', $this->translate('MSG_FIELD_IS_NOT_NULL'));
                }
                break;
            default:
                break;
        }
    }

    /**
     * Возвращает визуальный тип поля.
     *
     * @access public
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Устанавливает системный тип поля, одновременно устанавливая на
     * основании его визуальный тип.
     *
     * @access public
     * @param string $systemType
     * @return void
     */
    public function setSystemType($systemType) {
        $this->systemType = $systemType;
        $this->setType($this->convertType($systemType));
    }

    /**
     * Возвращает системный тип поля.
     *
     * @access public
     * @return string
     */
    public function getSystemType() {
        return $this->systemType;
    }

    /**
     * Устанавливает режим отображения поля.
     *
     * @access public
     * @param int $mode
     * @return void
     */
    public function setMode($mode) {
        $this->mode = $mode;
    }

    /**
     * Возвращает режим отображения поля.
     *
     * @access public
     * @return int
     */
    public function getMode() {
        return $this->mode;
    }

    /**
     * Устанавливает уровень прав на поле.
     *
     * @access public
     * @param int $rights
     * @return void
     */
    public function setRights($rights) {
        $this->rights = $rights;
    }

    /**
     * Возвращает уровень прав на поле.
     *
     * @access public
     * @return int
     */
    public function getRights() {
        return $this->rights;
    }

    /**
     * Добавляет свойство поля.
     *
     * @access public
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function addProperty($name, $value) {
        if ($name == 'nullable') {
            $value = (bool)$value;
        }
        $this->additionalProperties[$name] = $value;
    }

    /**
     * Удаляет свойство поля.
     *
     * @access public
     * @param string $name
     * @return void
     */
    public function removeProperty($name) {
        unset($this->additionalProperties[$name]);
    }

    /**
     * Возвращает список имен дополнительных свойств поля.
     *
     * @access public
     * @return array
     */
    public function getPropertyNames() {
        return array_keys($this->additionalProperties);
    }

    /**
     * Возвращает значение свойста поля.
     *
     * @access public
     * @param string $name
     * @return mixed
     */
    public function getPropertyValue($name) {
        $value = null;
        if (isset($this->additionalProperties[$name])) {
            $value = $this->additionalProperties[$name];
        }
        return $value;
    }

    /**
     * Конвертирует тип поля из системного типа в визуальный.
     *
     * @access protected
     * @param string $systemType
     * @return string
     */
    protected function convertType($systemType) {
        switch ($systemType) {
            case DBA::COLTYPE_STRING:
                if (strpos($this->name, '_password')) {
                    $result = self::FIELD_TYPE_PWD;
                }
                elseif (strpos($this->name, '_email')) {
                    $result = self::FIELD_TYPE_EMAIL;
                }
                elseif (strpos($this->name, '_phone')) {
                    $result = self::FIELD_TYPE_PHONE;
                }
                elseif (strpos($this->name, '_img')) {
                    $result = self::FIELD_TYPE_IMAGE;
                }
                elseif (strpos($this->name, '_file')) {
                    $result = self::FIELD_TYPE_FILE;
                }
                elseif (strpos($this->name, '_pfile')) {
                    $result = self::FIELD_TYPE_PFILE;
                }
                elseif (strpos($this->name, '_prfile')) {
                    $result = self::FIELD_TYPE_PRFILE;
                }
                else {
                    $result = self::FIELD_TYPE_STRING;
                }
                break;
            case DBA::COLTYPE_FLOAT:
                $result = self::FIELD_TYPE_FLOAT;
                break;
            case DBA::COLTYPE_INTEGER:
                if ($this->length == 1) {
                    $result = self::FIELD_TYPE_BOOL;
                }
                // обрабатываем внешний ключ
                elseif (is_array($this->getPropertyValue('key'))) {
                    $result = self::FIELD_TYPE_SELECT;
                }
                else {
                    $result = self::FIELD_TYPE_INT;
                }
                break;
            case DBA::COLTYPE_TEXT:
                if (strpos($this->name, '_rtf')) {
                    $result = self::FIELD_TYPE_HTML_BLOCK;
                }
                else {
                    $result = self::FIELD_TYPE_TEXT;
                }
                break;
            case DBA::COLTYPE_DATETIME:
                $result = self::FIELD_TYPE_DATETIME;
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
     * Пересечение мета-данных конфигурации и мета-данных, полученных из БД.
     *
     * @access public
     * @param FieldDescription основное описание
     * @param FieldDescription дополнительное описание
     *
     * @return FieldDescription
     * @static
     */
    public static function intersect(FieldDescription $primaryFieldDescription, FieldDescription $secondaryFieldDescription) {
        $type = $primaryFieldDescription->getType();
        $mode = $primaryFieldDescription->getMode();

        if (!is_null($type)) {
            //меняем тип
            $secondaryFieldDescription->setType($type);
        }
        if (!is_null($mode)) {
            $secondaryFieldDescription->setMode($mode);
        }
        $secondaryFieldDescription->isMultilanguage = $primaryFieldDescription->isMultilanguage || $secondaryFieldDescription->isMultilanguage();
        $properties = $secondaryFieldDescription->getPropertyNames();
        foreach ($properties as $propertyName) {
            $propertyValue = $primaryFieldDescription->getPropertyValue($propertyName);

            if (!is_null($propertyValue) && !($propertyName == 'title' && $propertyValue == 'FIELD_'.self::EMPTY_FIELD_NAME)) {
                if ($propertyName == 'message') {
                    $propertyValue = $primaryFieldDescription->translate($propertyValue);
                }

                $secondaryFieldDescription->addProperty($propertyName, $propertyValue);
            }
        }

        return $secondaryFieldDescription;
    }

    /**
     * Проверяет корректность переданных данных.
     *
     * @access public
     * @param mixed $data
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
     * Возвращает флаг мультиязычности данных.
     *
     * @access public
     * @return boolean
     */
    public function isMultilanguage() {
        return $this->isMultilanguage;
    }

    /**
	 * Загружает набор возможных значений поля.
	 *
	 * @access public
	 * @param mixed $values набор значений
	 * @param string $keyName имя поля-ключа
	 * @param string $valueName имя поля основного значения
	 * @return void
	 * @see QAL::select()
	 */
    public function loadAvailableValues($values, $keyName, $valueName) {
        $result = array();
        if (is_array($values)) {
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
        }
        $this->availableValues = $result;
    }

    /**
	 * Возвращает набор возможных значений поля.
	 *
	 * @access public
	 * @return array
	 */
    public function getAvailableValues() {
        return $this->availableValues;
    }

    /**
     * Определяет значение режима отображения элемента
     *
     * @return int
     * @access public
     * @static
     */

    public static function computeRights($methodRights, $RORights = null, $FCRights = null) {
        //Если уровень прав не указан, берем права документа
        $RORights = is_null($RORights)?$methodRights:$RORights;
        $FCRights = is_null($FCRights)?$methodRights:$FCRights;


        //Если права на чтение на контрол меньше чем права на метод, то контрол - невидим
        if($methodRights<$RORights) {
            $result = FieldDescription::FIELD_MODE_NONE;
        }
        //Если права на чтение на контрол больше или равны правам на метод, и права на запись меньше  - просто выводится текст контрола
        elseif($methodRights >= $RORights&&$methodRights<$FCRights)
        {
            $result = FieldDescription::FIELD_MODE_READ;
        }
        elseif($methodRights>=$FCRights)
        {
            $result = FieldDescription::FIELD_MODE_EDIT;
        }

        Return $result;
    }

}
