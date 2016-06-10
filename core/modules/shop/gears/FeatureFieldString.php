<?php

namespace Energine\shop\gears;
use Energine\share\gears\DataDescription, Energine\share\gears\FieldDescription;

class FeatureFieldString extends FeatureFieldAbstract {

	public function setValue($value) {
		$this -> value = (string)$value;
		return $this;
	}

	public function getValue() {
		return (string) $this -> value;
	}

	public function __toString() {
		return (!empty($this -> value)) ? (string) $this -> value . ($this -> getUnit() ? ' ' . $this -> getUnit() : '') : '';
	}

	public function modifyFormFieldDescription(DataDescription &$dd, FieldDescription &$fd) {
		$fd -> setType(FieldDescription::FIELD_TYPE_STRING);
	}
}