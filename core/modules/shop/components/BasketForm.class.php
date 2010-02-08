<?php
/**
 * Содержит класс BasketForm
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 * @copyright ColoCall 2006
 * @version $Id$
 */

//require_once('core/modules/share/components/DataSet.class.php');
//require_once('core/modules/shop/components/Basket.class.php');
//require_once('core/modules/shop/components/Discounts.class.php');
//require_once('core/modules/shop/components/CurrencyConverter.class.php');
/**
 * Корзина с выбранными продуктами
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 */
class BasketForm extends DataSet {
    /**
     * Объект - корзина
     *
     * @var Basket
     * @access private
     */
    private $basket;

    /**
     * @access private
     * @var Discounts скидки
     */
    private $discounts;

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
        $this->basket = Basket::getInstance();
        $this->discounts = Discounts::getInstance();
        $this->setType(self::COMPONENT_TYPE_LIST);
    }

    /**
	 * Выводим данные корзины
	 *
	 * @return void
	 * @access protected
	 */

    protected function main() {
        /*if ($component = $this->document->componentManager->getComponentByName('basketList')) {
        	$component->disable();
        }*/
        parent::main();
    }

    /**
      * Для поля product_id изменяем тип
      *
      * @return DataDescription
      * @access protected
      */

    protected function createDataDescription() {
        $result = parent::createDataDescription();
        $ProductIDFieldDescription = $result->getFieldDescriptionByName('product_id');
        $ProductIDFieldDescription->setType(FieldDescription::FIELD_TYPE_STRING);
        $ProductIDFieldDescription->setMode(FieldDescription::FIELD_MODE_READ);
        return $result;
    }

    /**
     * Загружаем цены
     *
     * @return array
     * @access protected
     */

    protected function loadData() {
        $result = $this->basket->getContents();
        //Подсчитываем сумму
        if (!empty($result)) {
            $this->setProperty('discount', $this->discounts->getDiscountForGroup());
            $this->setProperty('summ', $this->basket->getTotal());
            $this->setProperty('summ_with_discount', $this->basket->getTotal(true));
            $this->addTranslation('TXT_DISCOUNT');
            $this->addTranslation('TXT_BASKET_SUMM');
            $this->addTranslation('TXT_BASKET_SUMM_WITH_DISCOUNT');
            //Добавляем изображение
            /*
            foreach ($result as $key => $productInfo) {
            	$result[$key]['product_thumb_img'] = simplifyDBResult($this->dbh->select('shop_products', 'product_thumb_img', array('product_id'=>$productInfo['product_id'])), 'product_thumb_img', true);
            }
            */
        }
        else {
            //Если корзина пустая - добавляем перевод сообщения
        	$this->addTranslation('TXT_BASKET_EMPTY');
        }
        return $result;
    }
}
