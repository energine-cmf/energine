<?php

class ExtendedSaver extends Saver {
    /**
     * Имя первичного ключа таблицы
     *
     * @access private
     * @var string
     */
    private $pk;

    /**
     * Имя основной таблицы
     * @var string
     */
    private $mainTableName;

    /**
     * Устанавливает имя основной таблицы
     *
     * @return void
     * @access public
     */
    public function setDataDescription(DataDescription $dd) {
        parent::setDataDescription($dd);
        foreach ($dd as $fieldName => $fieldInfo) {
            if ($fieldInfo->getPropertyValue('key') === true) {
                $this->pk = $fieldName;
                $this->mainTableName = $fieldInfo->getPropertyValue('tableName');
                break;
            }
        }
    }

    /**
     * @return string
     */
    protected function getTableName() {
        return $this->mainTableName;
    }

    /**
     * @return string
     */
    protected function getPK(){
        return $this->pk;
    }

    /**
     * Сохраняем данные в таблицу аплоадсов и в таблицу тегов
     * @return void
     */
    public function save() {
        $result = parent::save();
        $entityID = ($this->getMode() == QAL::INSERT) ? $result
            : $this->getData()->getFieldByName($this->pk)->getRowData(0);
        $tm = new TagManager($this->dataDescription, $this->data, $this->mainTableName);
        $tm->save($entityID);

        $am = new AttachmentManager($this->dataDescription, $this->data, $this->mainTableName);
        $am->save($entityID);

        return $result;
    }
}
