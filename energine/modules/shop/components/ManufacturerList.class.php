<?php

/**
 * Содержит класс ManufacturerList
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 * @copyright ColoCall 2007
 * @version $Id$
 */

//require_once('core/modules/share/components/DataSet.class.php');

/**
 * Класс выводящий список производителей для раздела каталога
 *
 * @package energine
 * @subpackage core
 * @author dr.Pavka
 */
class ManufacturerList extends DataSet {
    /**
     * Конструктор класса
     *
     * @param string $name
     * @param string $module
     * @param Document $document
     * @param array $params
     * @access public
     */
    public function __construct($name, $module, Document $document,  array $params = null) {
        parent::__construct($name, $module, $document,  $params);
        $this->setType(self::COMPONENT_TYPE_LIST);
        $this->addTranslation('FIELD_PRODUCER');
    }

    /**
	 * Перечень полей
	 *
	 * @return DataDescription
	 * @access protected
	 */

    protected function createDataDescription() {
        $result = new DataDescription();
        $fd = new FieldDescription('producer_id');
        $fd ->setType(FieldDescription::FIELD_TYPE_INT);
        $fd->addProperty('key', true);
        $result->addFieldDescription($fd);

        $fd = new FieldDescription('producer_name');
        $fd ->setType(FieldDescription::FIELD_TYPE_STRING);
        $result->addFieldDescription($fd);

        $fd = new FieldDescription('producer_segment');
        $fd ->setType(FieldDescription::FIELD_TYPE_STRING);
        $result->addFieldDescription($fd);

        return $result;
    }

    /**
	  * Загрузка данных
	  *
	  * @return array
	  * @access protected
	  */

    protected function loadData() {
        $sitemap = Sitemap::getInstance();
        $smapID = $this->document->getID();
        $descendants = Sitemap::getInstance()->getTree()->getNodeById($smapID)->getDescendants()->asList(false);

        $id = implode(',', array_merge(array($smapID), $descendants));

        $result = $this->dbh->selectRequest('SELECT DISTINCT producer.producer_id, producer.producer_name, producer_segment '.
        'FROM shop_products product '.
        'LEFT JOIN shop_producers producer ON producer.producer_id = product.producer_id '.
        'WHERE product.smap_id in('.$id.')');
        $url  = $sitemap->getURLByID($smapID);

        if (is_array($result)) {
            foreach ($result as $key => $value) {
            	$result[$key]['producer_segment'] = $url.'manufacturer-'.$value['producer_segment'].'/';
            }
        }
        return $result;

    }
}