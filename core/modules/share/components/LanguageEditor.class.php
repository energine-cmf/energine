<?php

/**
 * Содержит класс TranslationEditor
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2006
 * @version $Id$
 */

//require_once('core/modules/share/components/Grid.class.php');

/**
 * Редактор переводов
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class LanguageEditor extends Grid {
    /**
     * Конструктор класса
     *
     * @return void
     */
    public function __construct($name, $module, Document $document,  array $params = null) {
        parent::__construct($name, $module, $document,  $params);
        $this->setTableName('share_languages');
        $this->setTitle($this->translate('TXT_LANGUAGE_EDITOR'));
        $this->setOrderColumn('lang_order_num');
        $this->setOrder(array('lang_order_num'=>QAL::ASC));
    }

    /**
     * Добавляем паттерн и сообщение об ошибке для описания поля lang_abbr. Поле должно содержать две маленькие латинские буквы
     *
     * @return DataDescription
     * @access protected
     */

     protected function createDataDescription() {
        $dataDescription = parent::createDataDescription();

        if ($this->getType() !== self::COMPONENT_TYPE_LIST) {
            $langAbbr = $dataDescription->getFieldDescriptionByName('lang_abbr');
            $langAbbr->addProperty('pattern', '/^[a-z]{2}$/');
            $langAbbr->addProperty('message', 'MSG_BAD_LANG_ABBR');
        }

        return $dataDescription;
     }

    /**
     * При создании нового языка не даем возможности сделать его дефолтным
     *
     * @return void
     * @access protected
     */

    protected function add() {
        parent::add();
        if($fd = $this->getDataDescription()->getFieldDescriptionByName('lang_default')){
            $fd->setMode(FieldDescription::FIELD_MODE_READ);
        }
        $field = new Field('lang_default');
        $field->setData(0);
        $this->getData()->addField($field);
    }

    /**
     * Переопределенный метод
     * Для формы редактирования, если чекбокс языка по умолчания отмечен делает его неактивным
     *
     * @return void
     * @access public
     */

    public function build() {
        if ($this->getType() == self::COMPONENT_TYPE_FORM_ALTER ) {
            //Если это язык по умолчанию - делаем неактивным
            if ($this->getData()->getFieldByName('lang_default')->getRowData(0) === true) {
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

    public function loadData() {
        $result = parent::loadData();
        if ($this->getAction() == 'save' && isset($result[0]['lang_default']) && $result[0]['lang_default'] !== '0') {
            $this->dbh->modify(QAL::UPDATE, $this->getTableName(), array('lang_default'=>null));
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

    public function deleteData($id) {
        //если мы пытаемся удалить текущий язык
        //генерим ошибку
        if ($this->document->getLang() == $id || $id == Language::getInstance()->getDefault()) {
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

    protected function saveData() {
        $langID = parent::saveData();
        if ($this->saver->getMode() == QAL::INSERT) {
            $this->dbh->modifyRequest('INSERT INTO share_sitemap_translation(smap_id, lang_id, smap_name) SELECT smap_id, %s, concat(\'--\',smap_name, \'--\') as smap_name from share_sitemap_translation WHERE lang_id = (select lang_id from share_languages where lang_default=1)', $langID);
            $this->dbh->modifyRequest('INSERT INTO share_lang_tags_translation(ltag_id, lang_id, ltag_value_rtf) SELECT ltag_id, %s, ltag_name from share_lang_tags', $langID);

            /**
             * @todo По хорошему, при добавлении нового языка нужно вносить данные во все _translation таблицы
             */

        }
        return $langID;
    }
}
