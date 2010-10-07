<?php

/**
 * Содержит класс LangSwitcher
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2006
 */

/**
 * переключатель языков
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
final class LangSwitcher extends DataSet {
    /**
     * Конструктор класса
     *
     * @return void
     */
    public function __construct($name, $module,  array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setType(self::COMPONENT_TYPE_LIST);
    }

    protected function createBuilder() {
        return new SimpleBuilder();
    }

    /**
     * Method Description
     *
     * @return type
     * @access protected
     */

    protected function loadData() {
        $lang = Language::getInstance();
        $data = $lang->getLanguages();

        foreach ($data as $langID => $LangInfo) {
            $result[$langID] = $LangInfo;
            $result[$langID]['lang_id'] = $langID;
            $result[$langID]['lang_url'] = $result[$langID]['lang_abbr'] . '/' .
                    Request::getInstance()->getPath(Request::PATH_WHOLE, true);
        }
        return $result;
    }

    /**
     * Создаем перечень полей
     *
     * @return DataDescription
     * @access protected
     */

    protected function createDataDescription() {
        $result = new DataDescription();
        $f = new FieldDescription('lang_id');
        $f->setType(FieldDescription::FIELD_TYPE_INT);
        $result->addFieldDescription($f);

        $f = new FieldDescription('lang_abbr');
        $f->setType(FieldDescription::FIELD_TYPE_STRING);
        $result->addFieldDescription($f);

        $f = new FieldDescription('lang_name');
        $f->setType(FieldDescription::FIELD_TYPE_STRING);
        $result->addFieldDescription($f);

        $f = new FieldDescription('lang_url');
        $f->setType(FieldDescription::FIELD_TYPE_STRING);
        $result->addFieldDescription($f);

        return $result;
    }


}
