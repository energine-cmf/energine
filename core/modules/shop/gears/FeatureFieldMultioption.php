<?php

namespace Energine\shop\gears;

use Energine\share\gears\DataDescription, Energine\share\gears\FieldDescription;
use Energine\share\gears\Field;

class FeatureFieldMultioption extends FeatureFieldOption {

    public function setValue($value) {
        if (empty($value)) {
            $this->value = [];
        } elseif (is_string($value)) {
            $this->value = array_filter(explode(',', $value), 'is_numeric');
        } elseif (is_array($value)) {
            $this->value = array_filter($value, 'is_numeric');
        } else {
            $this->value = [];
        }
        return $this;
    }

    public function getValue() {
        return $this->value;
    }

    public function __toString() {

        $result = [];
        if ($this->value) {
            foreach ($this->value as $value) {
                if (isset($this->options[$value]['value']))
                    $result[] = $this->options[$value]['value'];
            }
        }

        return (!empty($result)) ?
            implode(', ', $result) .
            (($this->getUnit()) ? ' ' . $this->getUnit() : '')
            : '';
    }

    public function modifyFormFieldDescription(DataDescription &$dd, FieldDescription &$fd) {
        $fd->setType(FieldDescription::FIELD_TYPE_MULTI);

        $fd->setAvailableValues([]);
        $values = [];
        if ($this->options) {
            foreach ($this->options as $option_id => $option_data) {
                $values[] = [
                    'id' => $option_id,
                    'value' => $option_data['value'],
                    'path' => $option_data['path'],
                    'mime_type' => $option_data['mime_type']
                ];
            }
        }
        $fd->loadAvailableValues($values, 'id', 'value');
    }

    public function modifyFormField(Field &$field) {
        $field->setRowData(0, $this->getValue());
    }

}