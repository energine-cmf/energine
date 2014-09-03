<?php
/**
 * @file
 * ExtendedFeed
 *
 * It contains the definition to:
 * @code
class ExtendedFeed;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2011
 *
 * @version 1.0.0
 */
namespace Energine\apps\components;
use Energine\share\components\DBDataSet, Energine\share\gears\FieldDescription, Energine\share\gears\Field, Energine\share\gears\AttachmentManager, Energine\share\gears\TagManager;
/**
 * Extended list.
 *
 * @code
class ExtendedFeed;
@endcode
 */
class ExtendedFeed extends Feed {
    /**
     * @copydoc Feed::defineParams
     */
    // Опция inline редактирования по умолчанию включена
    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            array(
                 'editable' => true,
            )
        );
    }

    /**
     * @copydoc DBDataSet::createDataDescription
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

    /**
     * @copydoc Feed::loadDataDescription
     */
    protected function loadDataDescription() {
        $res = parent::loadDataDescription();
        if (isset($res['smap_id'])) {
            $res['smap_id']['key'] = false;
        }
        return $res;
    }

    /**
     * @copydoc Feed::createData
     */
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

    /**
     * @copydoc Feed::main
     */
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
     * @copydoc Feed::view
     */
    protected function view() {
        $this->addFilterCondition(array('smap_id' => $this->document->getID()));
        DBDataSet::view();
        $this->addTranslation('BTN_RETURN_LIST');
        $am = new AttachmentManager($this->getDataDescription(), $this->getData(), $this->getTableName(), true);
        $am->createFieldDescription();
        $am->createField();
    }
}