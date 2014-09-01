<?php
/**
 * @file
 * ExtendedFeedEditor
 *
 * It contains the definition to:
 * @code
class ExtendedFeedEditor;
@endcode
 *
 * @author dr.Pavka
 * @copyright Energine 2011
 *
 * @version 1.0.0
 */
namespace Energine\apps\components;
use Energine\share\gears\ExtendedSaver, Energine\share\gears\TagManager, Energine\share\gears\SystemException, Energine\share\gears\JSONCustomBuilder;
/**
 * Media feed editor.
 *
 * @code
class ExtendedFeedEditor;
@endcode
 */
class ExtendedFeedEditor extends FeedEditor {
    /**
     * Publication field name-ID
     * Имя поля - идентификатора публикации
     * @var string $publishFieldName
     */
    private $publishFieldName = false;

    /**
     * @copydoc FeedEditor::__construct
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setSaver(new ExtendedSaver());
    }

    /**
     * @copydoc FeedEditor::setParam
     */
    protected function setParam($name, $value) {
        if ($name == 'tableName') {
            foreach (array_keys($this->dbh->getColumnsInfo($value)) as $columnName) {
                if (strpos($columnName, '_is_published')) {
                    $this->publishFieldName = $columnName;
                    $this->addTranslation('BTN_PUBLISH', 'BTN_UNPUBLISH');
                    break;
                }
            }
        }
        parent::setParam($name, $value);
    }

    /**
     * @copydoc FeedEditor::add
     */
    protected function add() {
        parent::add();
        $tm = new TagManager($this->getDataDescription(), $this->getData(), $this->getTableName());
        $tm->createFieldDescription();
    }

    /**
     * @copydoc FeedEditor::edit
     */
    protected function edit() {
        parent::edit();
        $tm = new TagManager($this->getDataDescription(), $this->getData(), $this->getTableName());
        $tm->createFieldDescription();
        $tm->createField();
    }
    /**
     * @copydoc FeedEditor::autoCompleteTags
     *
     * @throws SystemException 'ERR_NO_DATA'
     */
    protected function autoCompleteTags() {
        $b = new JSONCustomBuilder();
        $this->setBuilder($b);

        try {
            if (!isset($_POST['value'])) {
                throw new SystemException('ERR_NO_DATA', SystemException::ERR_CRITICAL);
            }
            else {

                $tags = TagManager::getTagStartedWith($_POST['value'], 10);
                $result['result'] = true;

                if(is_array($tags) && !empty($tags)){
                    foreach($tags as $tag){
                        $result['data'][] = array(
                            'key' => $tag,
                            'value' => $tag
                        );
                    }
                }
            }
        }
        catch (\Exception $e) {
            $result = array(
                'result' => false,
                'data' => false,
                'errors' => array(
                    
                )
            );
        }

        $b->setProperties($result);
    }

    /**
     * Publish material.
     */
    protected function publish() {
        list($id) = $this->getStateParams();
        $this->dbh->modifyRequest('UPDATE ' . $this->getTableName() . ' SET ' . $this->publishFieldName . ' = NOT ' . $this->publishFieldName . ' WHERE ' . $this->getPK() . ' = %s', $id);

        $b = new JSONCustomBuilder();
        $b->setProperties(
            array(
                'result' => true
            )
        );
        $this->setBuilder($b);
    }

}