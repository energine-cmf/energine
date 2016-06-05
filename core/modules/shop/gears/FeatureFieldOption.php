<?php

namespace Energine\shop\gears;
use Energine\share\gears\DataDescription,
	Energine\share\gears\FieldDescription,
	Energine\share\gears\Field;

class FeatureFieldOption extends FeatureFieldAbstract {

	public function setValue($value) {
		$this -> value = (int)$value; // fpv_id ?
		return $this;
	}

	public function getValue() {
		return $this -> value;
	}

	public function __toString() {
		return (!empty($this -> value) and isset($this->options[$this->value]['value'])) ?
			(string) $this -> options[$this -> value]['value'] .
				(($this -> getUnit()) ? ' ' . $this -> getUnit() : '')
			: '-';
	}

	public function modifyFormFieldDescription(DataDescription &$dd, FieldDescription &$fd) {
		$fd -> setType(FieldDescription::FIELD_TYPE_SELECT);

		$fd->setAvailableValues(array());
		$values = array();
		if ($this -> options) {
			foreach ($this -> options as $option_id => $option_data) {
				$values[] = array(
					'option_id' => $option_id,
					'option_value' => $option_data['value']
				);
			}
		}
		$fd->loadAvailableValues($values, 'option_id', 'option_value');

	}
}