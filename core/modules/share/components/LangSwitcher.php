<?php
/**
 * @file
 * LangSwitcher
 *
 * It contains the definition to:
 * @code
final class LangSwitcher;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2006
 *
 * @version 1.0.0
 */
namespace Energine\share\components;
use Energine\share\gears\SimpleBuilder, Energine\share\gears\Request, Energine\share\gears\DataDescription, Energine\share\gears\FieldDescription;
/**
 * language switcher.
 *
 * @code
final class LangSwitcher;
@endcode
 */
final class LangSwitcher extends DataSet {
    /**
     * @copydoc DataSet::__construct
     */
    public function __construct($name, $module,  array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setType(self::COMPONENT_TYPE_LIST);
    }

    /**
     * @copydoc DataSet::createBuilder
     */
    protected function createBuilder() {
        return new SimpleBuilder();
    }

    /**
     * @copydoc DataSet::loadData
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
     * Create data description.
     *
     * @return DataDescription
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
