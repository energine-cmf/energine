<?php
/**
 * Содержит класс MediaFeed
 *
 * @package energine
 * @subpackage apps
 * @author dr.Pavka
 * @copyright Energine 2011
 */

/**
 * Расширенный список
 *
 * @package energine
 * @subpackage apps
 * @author dr.Pavka
 */
class ExtendedFeed extends Feed {
    /**
     * Опция inline редактирования по умолчанию включена
     *
     * @return array
     */
    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            array(
                 'editable' => true,
            )
        );
    }

    /**
     *
     * @return DataDescription
     */
    protected function createDataDescription() {
        $res = DBDataSet::createDataDescription();
        if (!$res->getFieldDescriptionByName('smap_id')) {
            $f = new FieldDescription('smap_id');
            $f->setType(FieldDescription::FIELD_TYPE_INT)->setProperty('tableName', $this->getTableName());
            $res->addFieldDescription($f);
        }
        if (!$res->getFieldDescriptionByName('category')) {
            $f = new FieldDescription('category');
            $f->setType(FieldDescription::FIELD_TYPE_STRING);
            $res->addFieldDescription($f);
        }
        return $res;
    }

    protected function loadDataDescription() {
        $res = parent::loadDataDescription();
        if (isset($res['smap_id'])) {
            $res['smap_id']['key'] = false;
        }
        return $res;
    }

    protected function createData() {
        $res = parent::createData();
        if (!$res->isEmpty() && !($categoryField = $res->getFieldByName('category'))) {
            $categoryField = new Field('category');
            $res->addField($categoryField);
        }

        if ($f = $res->getFieldByName('smap_id')) {
            $map = E()->getMap();
            foreach ($f as $i => $row) {
                $catInfo = $map->getDocumentInfo($row);
                $categoryField->setRowData($i, $catInfo['Name']);
                $categoryField->setRowProperty($i, 'url', $map->getURLByID($row));
            }
        }
        return $res;
    }

    protected function main() {
        parent::main();
        $m = new AttachmentManager(
            $this->getDataDescription(),
            $this->getData(),
            $this->getTableName()
        );
        $m->createFieldDescription();
        $m->createField($this->getPK(), true);
        
        $m = new TagManager(
            $this->getDataDescription(),
            $this->getData(),
            $this->getTableName()
        );
        $m->createFieldDescription();
        $m->createField();
    }

    /**
     * View
     *
     * @return type
     * @access protected
     */

    protected function view() {
        parent::view();
        
        $am = new AttachmentManager($this->getDataDescription(), $this->getData(), $this->getTableName());
        $am->createFieldDescription();
        $am->createField();
    }
}