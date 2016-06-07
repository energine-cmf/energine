<?php
/**
 * Created by PhpStorm.
 * User: pavka
 * Date: 5/8/15
 * Time: 6:31 PM
 */

namespace Energine\share\gears;


trait DataBuilderWorker {
	/**
	 * Meta-data.
	 * @var DataDescription $dataDescription
	 */
	protected $dataDescription = null;

	/**
	 * Data.
	 * @var Data $data
	 */
	protected $data = null;


	/**
	 * Set meta-data.
	 *
	 * @param DataDescription $dataDescription Meta-data.
	 */
	public function setDataDescription( DataDescription $dataDescription ) {
		$this->dataDescription = $dataDescription;
	}

	/**
	 * Set data.
	 *
	 * @param Data $data Data.
	 */
	public function setData( Data $data ) {
		$this->data = $data;
	}

	protected function formatPhone( $fieldInfo, $fieldValue ) {
		$placeholder = $fieldInfo->getPropertyValue( 'phonePlaceholder' );
		$countryCode = $fieldInfo->getPropertyValue( 'phoneCode' );

		return E()->Utils->formatPhone($placeholder, $countryCode, $fieldValue);
	}
}