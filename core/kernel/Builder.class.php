<?php

/**
 * Класс Builder.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 * @copyright Energine 2006
 */


/**
 * Построитель XML-документа.
 *
 * @package energine
 * @subpackage core
 * @author 1m.dm
 */
class Builder extends AbstractBuilder {
    /**
     * Title
     *
     * @var string
     * @access private
     */
    private $title;

    /**
     * Конструктор класса.
     *
     * @param string recordset title
     * @access public
     * @return void
     */
    public function __construct($title = '') {
        parent::__construct();
        $this->title = $title;
    }

    /**
	 * Построение результата.
	 *
	 * @access protected
	 * @return void
	 */
    protected function run() {
        $dom_recordSet = $this->result->createElement('recordset');
        $this->result->appendChild($dom_recordSet);
        if ($this->data->isEmpty() || !$this->data->getRowCount()) {
        	$dom_recordSet->setAttribute('empty', 'empty');
        }
        $rowCount = 0;
        $i = 0;
        do {
            if (!$this->data->isEmpty()) {
                $rowCount = $this->data->getRowCount();
            }

            $dom_record = $this->result->createElement('record');

            foreach ($this->dataDescription as $fieldName => $fieldInfo) {
                $fieldProperties = false;
                if ($fieldInfo->getPropertyValue('tabName') === null) {
                    $fieldInfo->setProperty('tabName', $this->title);
                }

                // если тип поля предполагает выбор из нескольких значений - создаем соответствующие узлы
                if (in_array($fieldInfo->getType(), array(FieldDescription::FIELD_TYPE_MULTI, FieldDescription::FIELD_TYPE_SELECT))) {
                    if ($this->data && $this->data->getFieldByName($fieldName)) {
                        if ($fieldInfo->getType() == FieldDescription::FIELD_TYPE_SELECT) {
                        	$data = array($this->data->getFieldByName($fieldName)->getRowData($i));
                        }
                        else {
                            $data = $this->data->getFieldByName($fieldName)->getRowData($i);
                        }
                    }
                    else {
                    	$data = false;
                    }
                    $fieldValue = $this->createOptions($fieldInfo, $data);
                }
                elseif ($this->data->isEmpty()) {
                	$fieldValue = false;
                }
                elseif ($this->data->getFieldByName($fieldName)) {
                    $fieldProperties = $this->data->getFieldByName($fieldName)->getRowProperties($i);
                    $fieldValue = $this->data->getFieldByName($fieldName)->getRowData($i);
                }
                else {
                    $fieldValue = false;
                }

                $dom_field = $this->createField($fieldName, $fieldInfo, $fieldValue, $fieldProperties);
                $dom_record->appendChild($dom_field);
            }

            $dom_recordSet->appendChild($dom_record);
            $i++;
        }
        while ($i < $rowCount);
    }
}
