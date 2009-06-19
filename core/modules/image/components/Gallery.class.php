<?php
/**
 * Содержит класс Gallery
 *
 * @package energine
 * @subpackage image
 * @author dr.Pavka
 * @copyright Energine 2007
 * @version $Id$
 */

//require_once('core/modules/share/components/DBDataSet.class.php');

/**
 * Галерея изображений
 *
 * @package energine
 * @subpackage image
 * @author dr.Pavka
 */
class Gallery extends DBDataSet {

    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module
     * @param Document $document
     * @param array $params
     * @access public
     */
    public function __construct($name, $module, Document $document, array $params = null) {
        $params['active'] = true;
        parent::__construct($name, $module, $document, $params);
        $this->setTableName('image_photo_rubrics');
        $this->setFilter(array('smap_id'=>$this->document->getID()));
        $this->setParam('onlyCurrentLang', true);
        $this->setOrder(array('pr_order_num'=>QAL::ASC ));
    }

    /**
     * Метод выводит форму просмотра
     *
     * @return void
     * @access protected
     */

    protected function view() {
        $id = $this->getActionParams();
        //inspect($id);
        list($id) = $id;
        $id = simplifyDBResult($this->dbh->select($this->getTableName(), 'pr_id', array('pr_segment'=>$id)), 'pr_id', true);
        if ($id) {
            $this->setTableName('image_photo_gallery');
            $this->setFilter(array('pr_id'=>$id));
            $this->setParam('recordsPerPage', false);
            $this->setOrder(array('pg_order_num'=>QAL::DESC));
            $this->document->componentManager->getComponentByName('breadCrumbs')->addCrumb('', $id);
            $this->prepare();
            foreach ($this->getDataDescription()->getFieldDescriptions() as $fieldDescription) {
                $fieldDescription->setMode(FieldDescription::FIELD_MODE_READ);
            }
        }
        else {
        	$this->disable();
        }

    }
}