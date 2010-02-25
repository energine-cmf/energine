<?php
/**
 * Содержит класс BasketList
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 * @copyright ColoCall 2007
 * @version $Id$
 */

/**
 * Класс выводящий содержимое корзины, не для редактирования
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 */
class BasketList extends DataSet {
    /**
     * Объект - корзина
     *
     * @var Basket
     * @access private
     */
    private $basket;

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
        parent::__construct($name, $module, $document, $params);
        $this->basket = Basket::getInstance();
        //$this->discounts = Discounts::getInstance();
        $this->setType(self::COMPONENT_TYPE_LIST);
        $this->setTitle($this->translate('TXT_BASKET_CONTENTS'));
	}

	/**
	 * Загружаем данные
	 *
	 * @return array
	 * @access protected
	 */

	 protected function loadData() {
	    $result = $this->basket->getFormattedContents();
	    if (!empty($result)) {
            $this->addTranslation('TXT_BASKET_SUMM2');
            $this->setProperty('summ', $this->basket->getTotal());
	    }
	    else {
            //Если корзина пустая - добавляем перевод сообщения
        	$this->addTranslation('TXT_BASKET_EMPTY');
	    }
//inspect($result);
	    return $result;
	 }

}