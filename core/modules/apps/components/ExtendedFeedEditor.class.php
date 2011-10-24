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
     * Конструктор класса
     *
     * @param string $name
     * @param string $module

     * @param array $params
     * @access public
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setSaver(new ExtendedFeedSaver());
    }


    protected function add() {
        parent::add();
        $am = new AttachmentManager(
            $this->getDataDescription(),
            $this->getData(),
            $this->getTableName()
        );
        $am->createAttachmentTab();
        $tm = new TagManager($this->getDataDescription(), $this->getData(), $this->getTableName());
        $tm->createFieldDescription();
    }

    protected function edit() {
        parent::edit();
        $this->addAttFilesField($this->getTableName());
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

}