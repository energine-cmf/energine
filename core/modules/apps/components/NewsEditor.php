<?php
/**
 * @file
 * NewsEditor
 *
 * It contains the definition to:
 * @code
class NewsEditor;
 * @endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2007
 *
 * @version 1.0.0
 */
namespace Energine\apps\components;

use Energine\share\gears\DataDescription;
use Energine\share\gears\Field, Energine\share\gears\FieldDescription, Energine\share\gears\QAL, Energine\apps\gears\NewsEditorSaver;

/**
 * News editor.
 *
 * @code
class NewsEditor;
 * @endcode
 */
class NewsEditor extends ExtendedFeedEditor {
    /**
     * @copydoc ExtendedFeedEditor::__construct
     */
    public function __construct($name, array $params = NULL) {
        parent::__construct($name, $params);
        $this->setTableName('apps_news');
        $this->setOrder(['news_date' => QAL::DESC]);
        $this->setSaver(new NewsEditorSaver());
    }

    /**
     * @copydoc ExtendedFeedEditor::add
     */
    protected function add() {
        parent::add();
        $this->getDataDescription()->getFieldDescriptionByName('news_segment')->setType(FieldDescription::FIELD_TYPE_HIDDEN);
        $f = new Field('news_is_active');
        $f->setData(true, true);
        $this->getData()->addField($f);
    }


    protected function createDataDescription() {
        $result = parent::createDataDescription();
        if (in_array($this->getState(), ['add', 'edit'])) {
            $fd = new FieldDescription('news_is_top');
            $fd->setType(FieldDescription::FIELD_TYPE_BOOL);
            $fd->setProperty('tag', 'top');
            $result->addFieldDescription($fd, DataDescription::FIELD_POSITION_AFTER, $this->getPK());
        }
        return $result;
    }
}