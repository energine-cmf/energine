<?php

/**
 * Содержит класс TranslationEditor
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2006
 */


/**
 * Редактор переводов
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class LanguageEditor extends Grid
{
    /**
     * Конструктор класса
     *
     * @return void
     */
    public function __construct($name, $module, array $params = null)
    {
        parent::__construct($name, $module, $params);
        $this->setTableName('share_languages');
        $this->setTitle($this->translate('TXT_LANGUAGE_EDITOR'));
    }

    /**
     * Добавляем паттерн и сообщение об ошибке для описания поля lang_abbr. Поле должно содержать две маленькие латинские буквы
     *
     * @return DataDescription
     * @access protected
     */

    protected function createDataDescription()
    {
        $dataDescription = parent::createDataDescription();

        if ($this->getType() !== self::COMPONENT_TYPE_LIST) {
            $langAbbr =
                    $dataDescription->getFieldDescriptionByName('lang_abbr');
            $langAbbr->setProperty('pattern', '/^[a-z]{2}$/');
            $langAbbr->setProperty('message', 'MSG_BAD_LANG_ABBR');
        }

        return $dataDescription;
    }

    /**
     * При создании нового языка не даем возможности сделать его дефолтным
     *
     * @return void
     * @access protected
     */

    protected function add()
    {
        parent::add();
        if ($fd =
                $this->getDataDescription()->getFieldDescriptionByName('lang_default')) {
            $fd->setType(FieldDescription::FIELD_TYPE_HIDDEN);
        }
    }

    /**
     * Переопределенный метод
     * Для формы редактирования, если чекбокс языка по умолчания отмечен делает его неактивным
     *
     * @return void
     * @access public
     */

    public function build()
    {
        if ($this->getType() == self::COMPONENT_TYPE_FORM_ALTER) {
            //Если это язык по умолчанию - делаем неактивным
            if (
                $this->getData()->getFieldByName('lang_default')->getRowData(0) ===
                true) {
                $this->getDataDescription()->getFieldDescriptionByName('lang_default')->setMode(FieldDescription::FIELD_MODE_READ);
            }
        }

        return parent::build();
    }

    /**
     * Переопределенный метод сохранения
     *
     * @param array
     * @return void
     * @access public
     */

    public function loadData()
    {
        $result = parent::loadData();
        if ($this->getState() == 'save' && isset($result[0]['lang_default']) &&
            $result[0]['lang_default'] !== '0') {
            $this->dbh->modify(QAL::UPDATE, $this->getTableName(), array('lang_default' => null));
        }

        return $result;
    }

    /**
     * Переопределенный родительский метод
     *
     *
     * @return boolean
     * @access public
     */

    public function deleteData($id)
    {
        //если мы пытаемся удалить текущий язык
        //генерим ошибку
        if ($this->document->getLang() == $id ||
            $id == E()->getLanguage()->getDefault()) {
            throw new SystemException('ERR_CANT_DELETE', SystemException::ERR_CRITICAL);
        }
        parent::deleteData($id);
    }

    /**
     * При добавлении нового языка создаем задизейбленые разделы
     *
     * @return mixed
     * @access protected
     */

    protected function saveData()
    {
        if (isset($_POST[$this->getTableName()][$this->getPK()]) &&
            empty($_POST[$this->getTableName()][$this->getPK()])) {

            $_POST[$this->getTableName()]['lang_default'] = '0';
        }

        $langID = parent::saveData();
        if ($this->saver->getMode() == QAL::INSERT) {
            //При создании нового языка для всех таблиц переводов
            //создаем переводы копируя данные из дефолтного языка
            if ($translationTables =
                    $this->dbh->selectRequest('SHOW TABLES LIKE "%_translation"')) {
                $defaultLangID = E()->getLanguage()->getDefault();
                foreach ($translationTables as $row) {
                    $tableName = current($row);
                    $fields =
                            array_keys($this->dbh->getColumnsInfo($tableName));
                    $fields[1] = $langID;
                    $this->dbh->modifyRequest('
                        INSERT INTO ' . $tableName . ' SELECT ' .
                                              implode(',', $fields) . ' FROM ' .$tableName .
                                              ' WHERE lang_id=%s', $defaultLangID
                    );
                }
            }
        }
        return $langID;
    }
}
