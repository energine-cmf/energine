<?php
/**
 * Содержит класс Feed
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright Energine 2007
 * @version $Id$
 */

/**
 * Абстрактный класс предок для компонентов основывающихся на структуре сайта
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 */
class Feed extends DBDataSet
{
    /**
     * @var array | int
     * фильтр по идентификаторам
     */
    protected $filterID;

    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module

     * @param array $params
     * @access public
     */
    public function __construct($name, $module, array $params = null)
    {
        parent::__construct($name, $module, $params);
        //Если title не указан  - устанавливаем дефолтный
        if(!$this->getProperty('title')){
            $this->setProperty('title', $this->translate(
                        'TXT_' . strtoupper($this->getName())));
        }
        $this->setProperty('exttype', 'feed');
        $this->setParam('onlyCurrentLang', true);
        if ($this->getParam('editable') && $this->document->isEditable()) {
            $this->setProperty('editable', 'editable');
        }
        if ($this->getState() == 'main') {
            if (!($this->filterID = $this->getParam('id'))) {
                $this->filterID = $this->document->getID();
            }

            if ($this->getParam('showAll')) {
                $par = E()->getMap()->getTree()->getNodeById($this->filterID);
                $descendants = array();
                if($par){
                    $descendants = array_keys(
                        $par->getDescendants()->asList(false)
                    );
                }
                $this->filterID = array_merge(array($this->filterID), $descendants);
            }
            if ($orderParam = $this->getParam('orderField')) {
                if (is_array($orderParam)) {
                    $field = $orderParam[0];
                    $dir = (!empty($orderParam[1])) ? strtoupper($orderParam[1]) : QAL::ASC;
                } else {
                    $field = $orderParam;
                    $dir = QAL::ASC;
                }
                if (!in_array($dir, array(QAL::ASC, QAL::DESC))) $dir = QAL::ASC;
                $this->setOrder(array($field => $dir));
            }
            $this->addFilterCondition(array('smap_id' => $this->filterID));
            if ($limit = $this->getParam('limit')) {
                $this->setLimit(array(0, $limit));
                $this->setParam('recordsPerPage', false);
            }
        }
    }
    /*
     * @access protected
     * @return DataDescription
     * Якщо у конфігі є smap_id, то змінюємо його тип на int.
     * Потрібно для того, щоб при проходжені подальшого циклу ми не лізли в таблицю share_sitemap.
     * Натомість smap_id у нас реально буде містити тільки smap_id as integer
     * */
    protected function loadDataDescription(){
        $result = parent::loadDataDescription();
        if(isset($result['smap_id'])){
            $result['smap_id']['key'] = false;
        }
        return $result;
    }

    /**
     * Убираем smap_id
     *
     * @access protected
     * @return DataDescription
     * Якщо у конфігі не вказано smap_id, створюємо його, вказуємо таблицю з якою брати значення й додаємо до DataDescription.
     * Потім змінюємо тип smap_id на FIELD_TYPE_HIDDEN.
     * 
     */
    protected function createDataDescription()
    {
        $result = parent::createDataDescription();
        if ($this->getState() == 'main') {
            if (!($fd = $result->getFieldDescriptionByName('smap_id'))) {
                $fd = new FieldDescription('smap_id');
                $fd->setProperty('tableName',$this->getTableName());
                $result->addFieldDescription($fd);
            }
            $fd->setType(FieldDescription::FIELD_TYPE_HIDDEN);
        }
        return $result;
    }
    /*
     * Окрім батьківського методу ми також робимо деякі дії зі smap_id,
     * а саме записуємо йому властивістю url повне значення smap_segment.
     * Таким чином, якщо у нас на одній сторінці Feed витягне елементи з різними smap_id,
     * то ми завжди будемо знати як правильно сформувати link для того щоб дістатися до конкретного елементу.
     *  
     * */
    protected function main(){
        parent::main();
        if($f = $this->getData()->getFieldByName('smap_id')){
            foreach($f as $key=>$value){
                $f->setRowProperty($key,'url',E()->getMap()->getURLByID($value));
            }
        }
    }


    /**
     * Добавляем крошку
     *
     * @return void
     * @access protected
     */

    protected function view()
    {
        parent::view();
        $this->addFilterCondition(array('smap_id' => $this->document->getID()));
        $this->document->componentManager->getBlockByName('breadCrumbs')->addCrumb();
    }

    /**
     * Делаем компонент активным
     *
     * @return array
     * @access protected
     */

    protected function defineParams()
    {
        return array_merge(
            parent::defineParams(),
            array(
                 'active' => true,
                 'showAll' => false,
                 'id' => false,
                 'limit' => false,
                 'editable' => false,
                 'orderField' => false,
            )
        );
    }
}