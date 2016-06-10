<?php

namespace Energine\shop\gears;
use Energine\share\gears\DataDescription, Energine\share\gears\FieldDescription;

class FeatureFieldInt extends FeatureFieldAbstract {

	public function setValue($value) {
		$this -> value = (int)$value;
		return $this;
	}

	public function getValue() {
		return (int) $this -> value;
	}

	public function __toString() {
		return (!empty($this -> value)) ? ((int) $this -> value) . ($this -> getUnit() ? ' ' . $this -> getUnit() : '') : '';
	}

	public function modifyFormFieldDescription(DataDescription &$dd, FieldDescription &$fd) {
		$fd -> setType(FieldDescription::FIELD_TYPE_INT);
	}
}