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
		$fieldValue  = preg_replace( '/\D/', '', $fieldValue );

		if ( $placeholder && $fieldValue ) {
			$format      = str_replace( 'X', '%s', $placeholder );
			$countryCode = $fieldInfo->getPropertyValue( 'phoneCode' );

			if ( strlen( $fieldValue ) == 9 && $countryCode ) {
				$fieldValue = $countryCode . $fieldValue;
			} elseif ( strlen( $fieldValue ) != 12 ) {
				$fieldValue = str_pad( $fieldValue, 12, '0', STR_PAD_LEFT );
			}

			$format = str_repeat( '%s', strlen( $countryCode ) ) . $format;
			

			$fieldValue = vsprintf( '+' . $format, str_split( $fieldValue ) );
		}

		return $fieldValue;
	}
}