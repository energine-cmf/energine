<?php
/**
 * @file
 * ExtendedSaver.
 *
 * It contains the definition to:
 * @code
class ExtendedSaver;
@endcode
 *
 * @author
 *
 * @version 1.0.0
 */
namespace Energine\share\gears;
/**
 * Extended Saver.
 *
 * @code
class ExtendedSaver;
@endcode
 */
class ExtendedSaver extends Saver {
    /**
     * Primary key of the table.
     * @var string $pk
     */
    private $pk;

    /**
     * Main table name.
     * @var string $mainTableName
     */
    private $mainTableName;

    /**
     * Set data description and main table name.
     *
     * @param DataDescription $dd Data description.
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
     * Get main table name.
     *
     * @return string
     */
    protected function getTableName() {
        return $this->mainTableName;
    }

    /**
     * Get primary key.
     *
     * @return string
     */
    protected function getPK(){
        return $this->pk;
    }

    /**
     * Save data into the table of uploads and tags.
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
