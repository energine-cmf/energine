<?php

namespace Energine\shop\gears;

use Energine\share\gears\DataDescription, Energine\share\gears\FieldDescription;

class FeatureFieldBool extends FeatureFieldAbstract {

    public function setValue($value) {
        $this->value = (bool)$value;
        return $this;
    }

    public function getValue() {
        return (bool)$this->value;
    }

    public function __toString() {
        return $this->translate((!empty($this->value)) ? 'TXT_YES' : 'TXT_NO');
    }

    public function modifyFormFieldDescription(DataDescription &$dd, FieldDescription &$fd) {
        $fd->setType(FieldDescription::FIELD_TYPE_BOOL);
    }
}