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
        $lang = E()->getLanguage();
        $data = $lang->getLanguages();

        foreach ($data as $langID => $langInfo) {
            $abbr = $langInfo['lang_abbr'];
            if($langInfo['lang_default']){
                $langInfo['lang_abbr'] = '';
            }
            $result[$langID] = $langInfo;
            $result[$langID]['lang_real_abbr'] = $abbr;
            $result[$langID]['lang_id'] = $langID;
            $result[$langID]['lang_url'] = $result[$langID]['lang_abbr'] . (($result[$langID]['lang_abbr'])?'/':'') .
                    E()->getRequest()->getPath(Request::PATH_WHOLE, true);
        }
        //inspect($result);
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

        $f = new FieldDescription('lang_real_abbr');
        $f->setType(FieldDescription::FIELD_TYPE_STRING);
        $result->addFieldDescription($f);

        return $result;
    }


}
