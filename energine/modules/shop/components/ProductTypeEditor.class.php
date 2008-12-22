<?php
/**
 * Содержит класс ProductTypeEditor
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 * @copyright ColoCall 2006
 * @version $Id$
 */

//require_once('core/modules/share/components/Grid.class.php');

/**
 * Редатор типов товаров
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 */
class ProductTypeEditor extends Grid {
    /**
     * Редактор параметров
     *
     * @var ProductParamsEditor
     * @access private
     */
    private $paramsEditor = false;
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
        $this->setTableName('shop_product_types');
        $this->setOrder(array('pt_name' => QAL::ASC));
    }

    /**
	 * Выводит компонент - редактор параметров
	 *
	 * @return void
	 * @access protected
	 */

    protected function showParams() {
        $id = $this->request->getPath(Request::PATH_ACTION);
        $id = $id[0];
        $this->request->setPathOffset($this->request->getPathOffset() + 2);
        $this->paramsEditor = $this->document->componentManager->createComponent('paramsEditor', 'shop', 'ProductParamsEditor', array('productTypeID' => $id), false);
        $this->paramsEditor->getAction();
        $this->paramsEditor->run();
    }

    /**
     * Для метода showParams вызывается свой билдер
     *
     * @return DOMNode
     * @access public
     */

    public function build() {
        if ($this->getAction() == 'showParams') {
            $result = $this->paramsEditor->build();
        }
        else {
        	$result = parent::build();
        }

        return $result;
    }

}
