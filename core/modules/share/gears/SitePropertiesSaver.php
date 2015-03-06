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
namespace Energine\share\gears;
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
        if($fPropNameField = $this->getData()->getFieldByName('prop_name')) {
            $name = Translit::transliterate($fPropNameField->getRowData(0), '_');
	        $data->loadInto($fPropNameField, preg_replace("/[^A-Za-z0-9_]/", '', $name));
        }
    }

    /**
     * Save data into the table of uploads and tags.
     */
    public function save() {

        $sIdField = $this->getData()->getFieldByName('site_id');
        $siteId = intval($sIdField->getRowData(0));
        $propName = $this->getData()->getFieldByName('prop_name')->getRowData(0);

        if($siteId
            && $this->getMode() !== QAL::UPDATE) {
            $propCount = (int)$this->dbh->getScalar('SELECT COUNT(prop_id) FROM share_sites_properties WHERE prop_name = %s AND (site_id = %s OR site_id IS NULL)', $propName, $siteId);
            // If there is no property, we need to insert "default" property with NULL as site id
            if($propCount === 0) {
	            $this->getData()->loadInto($sIdField, '');
            }
            elseif($propCount > 1) {
                throw new SystemException('ERR_PROPERTY_EXIST');
            }
        }

        return parent::save();
    }
}
