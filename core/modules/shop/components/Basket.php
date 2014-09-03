<?php
/**
 * Содержит класс Basket
 *
 * @package energine
 * @subpackage shop
 * @author Andrii A
 * @copyright Energine 2013
 */

/**
 * Корзина товаров
 *
 * @package energine
 * @subpackage shop
 * @author Andrii A
 */
class Basket extends DBDataSet {

    /**
     * User session Id
     *
     * @var int
     * @access private
     */
    private $sessionId;

    /**
     * Конструктор класса
     *
     * @access public
     */
    public function __construct($name, $module, array $params = null) {
        $params['active'] = true;
        parent::__construct($name, $module, $params);
        $this->setTableName('shop_basket');
        // Т.к. метод loadData перепесин, то
        // необходимо добавить фильтр по lang_id
        // вручную.
        $this->addFilterCondition(array('lang_id' => $this->document->getLang()));
        // Если неавторизированный пользователь добавил
        // товар в корзину, следует стартовать сессию для возможности
        // сохранения списка добавленых товаров.
        // @TODO: В силу того, что сессия является валидной только при наличии
        // данных, т.е. ИД пользователя, следует изменить класс UserSession для
        // возможности создания сессий для незарегестрированнхы пользователей.
        // @TODO: Также следует придумать механизм для удаления данных о покупках при удалении пользовательской сессии
        if(($this->getState() == 'put')
            && !UserSession::isOpen()) {
            call_user_func_array(
                array($this->response, 'addCookie'),
                $cookieInfo = UserSession::manuallyCreateSessionInfo()
            );
            $this->sessionId = $cookieInfo[1];
        }
        if($this->sessionId
            || ($this->sessionId = UserSession::isOpen())) {
            $this->addFilterCondition(array('session_native_id' => $this->sessionId));
        }
        else {
            $this->addFilterCondition(array('session_native_id IS NULL'));
        }
    }

    protected function main() {
        parent::main();
        $this->setProperty('basketTotal', $this->getTotal());
        E()->getController()->getTransformer()->setFileName('single_basket.xslt');
    }

    protected function init() {
        $this->js = $this->buildJS();
        $this->setBuilder(new EmptyBuilder());
    }

    /**
     * Добавление товара в корзину
     *
     * @param int идентификатор продукта
     * @param int количество позиций
     * @return void
     * @access public
     */
    public function put($productID, $productQuantity = 1) {
        $this->dbh->modify(
            'INSERT INTO ' . $this->getTableName()
            . ' VALUES (%s, %s, %s)'
            . ' ON DUPLICATE KEY UPDATE sb_quantity = sb_quantity + %3$s'
            , intval($productID), $this->sessionId, intval($productQuantity)
        );
        $this->prepare();
        $this->setBuilder(new JSONBuilder());
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
    }

    /**
     * Удаляет товар из корзины
     *
     * @param int идентификатор продукта
     * @return void
     * @access public
     */
    public function takeOut($productID) {
    }

    /**
     * Очищает корзину
     *
     * @return void
     * @access public
     */
    public function purify() {
    }

    protected function loadData() {
        $data = $this->dbh->select('SELECT p.product_id, b.sb_quantity, pt.product_title, p.product_price
                                            FROM shop_basket b
                                            LEFT JOIN shop_product p ON b.product_id = p.product_id
                                            LEFT JOIN shop_product_translation pt ON p.product_id = pt.product_id'
            . $this->dbh->buildWhereCondition($this->getFilter())
            . $this->dbh->buildOrderCondition($this->getOrder())
            . $this->dbh->buildLimitStatement($this->pager->getLimit()));
        return $data;
    }

    /**
     * Возвращает суммарную стоимость товаров в корзине с учетом скидки.
     *
     * @return float
     * @access public
     */
    private function getTotal() {
        $total = 0;
        if(!$this->getData()->isEmpty()) {
            $priceField = $this->getData()->getFieldByName('product_price');
            $quantityField = $this->getData()->getFieldByName('sb_quantity');
            foreach($priceField as $index => $value) {
                $total += $quantityField->getRowData($index) * floatval($value);
            }
        }
        return $total;
    }
}