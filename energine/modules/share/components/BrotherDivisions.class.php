<?php


/**
 * Содержит класс BrotherDivisions
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 * @copyright ColoCall 2006
 * @version $Id$
 */


//require_once('core/modules/share/components/ChildDivisions.class.php');

/**
 * Класс передназначен для вівода дочерних разделов текущего раздела
 *
 * @package energine
 * @subpackage share
 * @author dr.Pavka
 *
 */
class BrotherDivisions extends ChildDivisions {
    /**
     * Конструктор класса
     *
     * @return void
     */
    public function __construct($name, $module, Document $document,  array $params = null) {
        $params['active'] = false;
        parent::__construct($name, $module, $document,  $params);
        $this->setParam('recordsPerPage', false);
    }
    /**
     * Убираем DescriptionRtf
     *
     * @return DataDescription
     * @access protected
     */

     protected function createDataDescription() {
        $result = parent::createDataDescription();
        $result->removeFieldDescription($result->getFieldDescriptionByName('DescriptionRtf'));
        return $result;
     }
    /**
	 * Переопределенный метод загрузки данных
	 *
	 * @return mixed
	 * @access protected
	 */

    protected function loadData() {
        $sitemap = Sitemap::getInstance();


        if (!$this->getParam('id')) {
            $id = $this->document->getID();
        }
        else {
            $id = $this->getParam('id');
        }
        $parentID = $sitemap->getDocumentInfo($id);
        $parentID = $parentID['Pid'];
        if (isset($parentID)) {
            $data = $sitemap->getChilds($parentID);
        }


        $data = (empty($data))?false:$data;
        if(is_array($data)) {
            foreach ($data as $id => $current) {
                $data[$id] = array(
                'Id' => $id,
                'Segment' => $current['Segment'],
                'Name' => $current['Name']
                );
            }

        }

        return $data;
    }
}
