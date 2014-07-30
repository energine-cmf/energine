<?php
/**
 * @file
 * SitePropertiesSaver
 *
 * It contains the definition to:
 * @code
class SitePropertiesSaver;
@endcode
 *
 * @author Andrii A
 * @copyright Energine 2014
 *
 * @version 1.0.0
 */

/**
 * Saver for site properties. Transliterates property name for each new property.
 *
 * @code
class SitePropertiesSaver;
@endcode
 */
class SitePropertiesSaver extends ExtendedSaver {
    public function setData(Data $data) {
        parent::setData($data);
        if($fPropName = $this->getData()->getFieldByName('prop_name')) {
            $fPropName->setData(Translit::transliterate($fPropName->getRowData(0), '_'), true);
        }
    }
}
