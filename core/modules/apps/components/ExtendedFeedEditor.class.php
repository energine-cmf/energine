<?php
/**
 * Содержит класс MediaFeedEditor
 *
 * @package energine
 * @subpackage apps
 * @author dr.Pavka
 * @copyright Energine 2011
 */

/**
 * Редактор медиа фида
 *
 * @package energine
 * @subpackage apps
 * @author dr.Pavka
 */
class ExtendedFeedEditor extends FeedEditor {

    /**
     * Имя поля - идентификатора публикации
     *
     * @access private
     * @var string
     */
    private $publishFieldName = false;

    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module

     * @param array $params
     * @access public
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setSaver(new ExtendedSaver());
    }

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

    protected function add() {
        parent::add();
        $tm = new TagManager($this->getDataDescription(), $this->getData(), $this->getTableName());
        $tm->createFieldDescription();
    }

    protected function edit() {
        parent::edit();
        $tm = new TagManager($this->getDataDescription(), $this->getData(), $this->getTableName());
        $tm->createFieldDescription();
        $tm->createField();
    }
    /**
     *
     * @throws SystemException
     * @return void
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
        catch (Exception $e) {
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
     * Публицация \ депубликация материала.
     *
     * @return void
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