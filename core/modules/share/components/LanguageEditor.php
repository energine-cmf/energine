<?php
/**
 * @file
 * LanguageEditor
 *
 * It contains the definition to:
 * @code
class LanguageEditor;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\components;
use Energine\share\gears\FieldDescription;
/**
 * Language editor.
 *
 * @code
class LanguageEditor;
@endcode
 */
class LanguageEditor extends Grid {
    /**
     * @copydoc Grid::__construct
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setTableName('share_languages');
        $this->setTitle($this->translate('TXT_LANGUAGE_EDITOR'));
    }

    /**
     * @copydoc Grid::createDataDescription
     */
    // Добавляем паттерн и сообщение об ошибке для описания поля lang_abbr. Поле должно содержать две маленькие латинские буквы
    protected function createDataDescription() {
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
     * @copydoc Grid::add
     */
    // При создании нового языка не даем возможности сделать его дефолтным
    protected function add() {
        parent::add();
        if ($fd =
                $this->getDataDescription()->getFieldDescriptionByName('lang_default')) {
            $fd->setType(FieldDescription::FIELD_TYPE_HIDDEN);
        }
    }

    /**
     * @copydoc Grid::build
     */
    // Для формы редактирования, если чекбокс языка по умолчания отмечен делает его неактивным
    public function build() {
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
     * @copydoc Grid::loadData
     */
    public function loadData() {
        $result = parent::loadData();
        if ($this->getState() == 'save' && isset($result[0]['lang_default']) &&
            $result[0]['lang_default'] !== '0') {
            $this->dbh->modify(QAL::UPDATE, $this->getTableName(), array('lang_default' => null));
        }

        return $result;
    }

    /**
     * @copydoc Grid::deleteData
     */
    public function deleteData($id) {
        //если мы пытаемся удалить текущий язык
        //генерим ошибку
        if ($this->document->getLang() == $id ||
            $id == E()->getLanguage()->getDefault()) {
            throw new SystemException('ERR_CANT_DELETE', SystemException::ERR_CRITICAL);
        }
        parent::deleteData($id);
    }

    /**
     * @copydoc Grid::saveData
     */
    // При добавлении нового языка создаем задизейбленые разделы
    protected function saveData() {
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
