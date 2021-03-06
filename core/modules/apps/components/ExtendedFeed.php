<?php
/**
 * @file
 * ExtendedFeed
 *
 * It contains the definition to:
 * @code
class ExtendedFeed;
 * @endcode
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
 * @endcode
 */
class ExtendedFeed extends Feed {
    /**
     * @copydoc Feed::defineParams
     */
    // Опция inline редактирования по умолчанию включена
    protected function defineParams() {
        return array_merge(
            parent::defineParams(),
            [
                'editable' => true,
                'tags' => false
            ]
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

    protected function loadData() {
        if ($tags = $this->getParam('tags')) {
            if (!($tagFilter = TagManager::getFilter(TagManager::getID($tags), $this->getTableName()))) {
                return false;
            }
            $this->addFilterCondition([$this->getTableName().'.'.$this->getPK() => $tagFilter]);
        }
        $result = parent::loadData();
        return $result;
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

        /*if ($f = $res->getFieldByName('smap_id')) {
            $map = E()->getMap();
            foreach ($f as $i => $row) {
                $catInfo = $map->getDocumentInfo($row);
                $f->setRowData($i, $catInfo['Name']);
                $f->setRowProperty($i, 'url', $map->getURLByID($row));
            }
        }*/
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
        $this->addFilterCondition(['smap_id' => $this->document->getID()]);
        DBDataSet::view();
        $this->addTranslation('BTN_RETURN_LIST');
        $am = new AttachmentManager($this->getDataDescription(), $this->getData(), $this->getTableName(), true);
        $am->createFieldDescription();
        $am->createField();
        if ($f = $this->getData()->getFieldByName('smap_id')) {
            foreach ($f as $key => $value) {
                $site = E()->getSiteManager()->getSiteByPage($value);
                $f->setRowProperty($key, 'url', E()->getMap($site->id)->getURLByID($value));
                $f->setRowProperty($key, 'base', $site->base);
            }
        }
    }
}