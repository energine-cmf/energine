<?php
/**
 * @file
 * GridExtender
 *
 * Contains the definition to:
 * @code
class GridExtender;
 * @endcode
 *
 * @author    dr.Pavka
 * @copyright Energine 2016
 *
 * @version   1.0.0
 */

namespace Energine\share\gears;


trait GridExtender {
	protected function createDataDescription(){
		/**
		 * @var $dd DataDescription
		 */
		$dd = parent::createDataDescription();
		if ($fd = $dd->getFieldDescriptionsByType(FieldDescription::FIELD_TYPE_PHONE)){
			if(!empty($fd)){
				$country = E()['EnergineSite\\webworks\\gears\\Countries']->getCountryByID(E()->getSiteManager()->getDefaultSite()->countryId);
				array_walk($fd, function($fd) use($country) {
					$fd->setProperty('phonePlaceholder', $country['TelFormat'])->setProperty('phoneCode', $country['TelCode']);
				});
			}
		}
		return $dd;
	}
}