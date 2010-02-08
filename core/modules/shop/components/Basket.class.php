<?php
/**
 * Содержит класс Basket
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 * @copyright ColoCall 2006
 * @version $Id$
 */

//require_once('core/framework/DBWorker.class.php');
//require_once('core/modules/shop/components/Discounts.class.php');
//require_once('core/modules/shop/components/CurrencyConverter.class.php');

/**
 * Корзина с выбранными продуктами
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 */
class Basket extends DBWorker {

	/**
	 * @access private
	 * @static
	 * @var Basket единый для всей системы экземпляр класса Basket
	 */
	private static $instance;

	/**
	 * Имя таблицы содержащей данные корзины
	 *
	 * @var string
	 * @access private
	 */
	private $tableName;

	/**
	 * Идентификатор пользовательского сеанса
	 *
	 * @var int
	 * @access private
	 */
	private $id;

	/**
	 * Содержание корзины
	 *
	 * @var array
	 * @access private
	 */
	private $contents = array();

	/**
	 * @access private
	 * @var Discounts скидки
	 */
	private $discounts;

	/**
	 * Конструктор класса
	 *
	 * @access public
	 */
	public function __construct() {
		parent::__construct();
		$this->tableName = 'shop_basket';
		$this->id = UserSession::getInstance()->getID();
		$this->discounts = Discounts::getInstance();

		$this->refresh();
	}

	private function refresh(){
		if(isset($_POST['basket'])){
			$basketData = $_POST['basket'];

			if(isset($basketData['add']) && is_array($basketData['add'])){
				foreach($basketData['add'] as $productID => $productCount){
					$this->put((int)$productID, (int)$productCount);
				}
			}
		    elseif(isset($basketData['delete']) && is_array($basketData['deleteItems'])) {
                foreach ($basketData['deleteItems'] as $productID) {
                    $this->takeOut((int)$productID);
                }
            }
			elseif(isset($basketData['update']) && is_array($basketData['update'])){
				foreach($basketData['update'] as $productID => $productCount){
					$this->update((int)$productID, (int)$productCount);
				}
			}
			
		}
		 

	}

	/**
	 * Возвращает единый для всей системы экземпляр класса UserSession.
	 *
	 * @access public
	 * @static
	 * @return Basket
	 */
	public static function getInstance() {
		if (!isset(self::$instance)) {
			self::$instance = new Basket;
		}
		return self::$instance;
	}
	/**
	 * Возвращает имя таблицы
	 *
	 * @return string
	 * @access protected
	 */

	protected function getTableName() {
		return $this->tableName;
	}

	/**
	 * Добавление товара в корзину
	 *
	 * @param int идентификатор продукта
	 * @param int количество позиций
	 * @return void
	 * @access public
	 */

	public function put($productID, $productCount = 1) {
		$this->dbh->modifyRequest(
        'INSERT INTO '.$this->getTableName().' (product_id, session_id, basket_count) VALUES (%s, %s, %s) '.
        'ON DUPLICATE KEY UPDATE basket_count=basket_count+1',
		$productID,
		$this->id,
		$productCount
		);
	}

	/**
	 * Изменяет количество позиций продукта
	 *
	 * @param int идентификатор продукта
	 * @param int количество позиций
	 * @return void
	 * @access public
	 */

	public function update($productID, $productCount) {
		if($productCount) {
			$this->dbh->modify(QAL::UPDATE , $this->getTableName(), array('basket_count' => $productCount), array('session_id'=>$this->id, 'product_id'=>$productID));
		}
		else {
			$this->dbh->modify(QAL::DELETE, $this->getTableName(), null, array('product_id' => $productID));
		}
	}

	/**
	 * Удаляет товар из корзины
	 *
	 * @param int идентификатор продукта
	 * @return void
	 * @access public
	 */

	public function takeOut($productID) {
		$this->dbh->modify(QAL::DELETE, $this->getTableName(), null, array('session_id'=>$this->id, 'basket_id'=>$productID));
	}

	/**
	 * Очищает корзин
	 *
	 * @return void
	 * @access public
	 */

	public function purify() {
		$this->dbh->modify(QAL::DELETE, $this->getTableName(), null, array('session_id'=>$this->id));
	}


	/**
	 * Возвращает суммарную стоимость товаров в корзине с учетом скидки.
	 *
	 * @return float
	 * @access public
	 */

	public function getTotal($withDiscount = false) {
		$contents = $this->getContents(false);
		$summ = 0;
		if(is_array($contents)) {
			foreach ($contents as $row) {
				if ($withDiscount) {
					$summ += $this->discounts->calculateCost($row['product_summ']);
				}
				else {
					//inspect($row['product_summ']);
					$summ += $row['product_summ'];
				}
			}
		}

		$converter = CurrencyConverter::getInstance();
		$HRNID = $converter->getCurrent();

		return $converter->format($converter->convert($summ, $HRNID, $contents[0]['curr_id']), $HRNID);
	}

	/**
	 * Возвращает содержимое корзины
	 *
	 * @param bool
	 * @return array
	 * @access public
	 * @see QAL::select()
	 */

	public function getContents($formattedOutput = true) {
		$result = $this->dbh->selectRequest(
        'SELECT main.*, pt.product_name, ext.product_price, ext.product_price*main.basket_count as product_summ, ext.product_price * (1 - dscnt.dscnt_percent / 100) AS product_summ_with_discount, ext.curr_id, product.product_segment, product.product_code '.
        'FROM '.$this->getTableName().' main '.
        'LEFT JOIN shop_products product ON product.product_id = main.product_id '.
        'LEFT JOIN shop_products_translation pt ON pt.product_id = main.product_id '.
        'LEFT JOIN shop_product_external_properties ext ON ext.product_code = product.product_code '.
        'LEFT JOIN shop_discounts dscnt ON dscnt.group_id = '.$this->discounts->getDefaultGroup().' '.
        'WHERE pt.lang_id = %s AND session_id = %s',
		Language::getInstance()->getCurrent(),
		$this->id
		);

		if (is_array($result)) {
			if($formattedOutput) $result = array_map(array($this, 'prepare'), $result);

			$this->contents = $result;
		}
		else {
			$this->contents = false;
		}

		return $this->contents;
	}

	/**
	 * Обработка корзины
	 *
	 * @return array
	 * @access private
	 */

	private function prepare($row) {
		$converter = CurrencyConverter::getInstance();
		$HRNID = $converter->getCurrent();
		$row['product_price'] = $converter->format($converter->convert($row['product_price'], $HRNID, $row['curr_id']),$HRNID);
		$row['product_summ'] = $converter->format($converter->convert($row['product_summ'], $HRNID, $row['curr_id']),$HRNID);
		return $row;
	}
}
