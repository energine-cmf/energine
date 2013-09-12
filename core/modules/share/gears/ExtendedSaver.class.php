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

        // обновление записей из _uploads таблицы, в которых PK = NULL по ID сессии
        if ($result && $this->dbh->tableExists($this->getTableName() . AttachmentManager::ATTACH_TABLE_SUFFIX)) {
            $id = (is_int($result)) ? $result : (int)$_POST[$this->getTableName()][$this->getPK()];
            //throw new SystemException($id);
            $this->dbh->modify(
                QAL::UPDATE,
                $this->getTableName() . AttachmentManager::ATTACH_TABLE_SUFFIX,
                array(
                    $this->getPK() => $id
                ),
                array(
                    $this->getPK() => null,
                    'session_id' => session_id()
                )
            );
        }


        return $result;
    }
}
