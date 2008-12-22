<?php
/**
 * Содержит класс ProductDivisions
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 * @copyright ColoCall 2006
 * @version $Id$
 */

//require_once('core/modules/share/components/ChildDivisions.class.php');
//require_once('core/modules/shop/components/ProductStatusEditor.class.php');

/**
 * Список разделов магазина
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 */
class ProductDivisions extends ChildDivisions {
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
        $this->setParam('recordsPerPage', false);
    }

    /**
     * Параметр active выставлен в false
     *
     * @return array
     * @access protected
     */

     protected function defineParams() {
        $result = array_merge(parent::defineParams(),
        array(
        'active'=>false,
        ));
        return $result;
     }
    /**
	 * Добавлено поле количество продуктов
	 *
	 * @return DataDescription
	 * @access protected
	 */

    protected function createDataDescription() {
        $result = parent::createDataDescription();
        $productCountFD = new FieldDescription('product_count');
        $productCountFD->setType(FieldDescription::FIELD_TYPE_INT);
        $result->addFieldDescription($productCountFD);
        return $result;
    }

    /**
	  * Добавляем значения количества продуктов
	  *
	  * @return array
	  * @access protected
	  */

    protected function loadData() {
        $result = parent::loadData();
        if (is_array($result)) {
            $tree = Sitemap::getInstance()->getTree();
            foreach ($result as $smapID => $row) {
                $descendants = array_keys($tree->getNodeById($smapID)->getDescendants($smapID)->asList(false));
                $id = array_merge(array($smapID), $descendants);
                $result[$smapID]['product_count'] = simplifyDBResult($this->dbh->select('shop_products', array('COUNT(product_id) as product_count'), array('smap_id'=>$id, 'ps_id'=>ProductStatusEditor::getVisibleStatuses($this->document->getRights()))), 'product_count', true);
            }
        }
        return $result;
    }
}
