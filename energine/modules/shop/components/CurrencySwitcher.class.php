<?php

/**
 * Содержит класс CurrencySwitcher
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 * @copyright ColoCall 2007
 * @version $Id$
 */

//require_once('core/modules/share/components/DBDataSet.class.php');
//require_once('core/modules/shop/components/CurrencyConverter.class.php');

/**
 * Переключатель валют
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 */
class CurrencySwitcher extends DBDataSet {
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
        $this->setTableName('shop_currency');
        $this->setTitle($this->translate('TXT_CURRENT_CURRENCY'));
        $this->setParam('recordsPerPage', false);
        $this->setParam('onlyCurrentLang', true);
	}

    /**
	 * Добавлен параметр id - идентификатор страницы
	 *
	 * @return int
	 * @access protected
	 */

    protected function defineParams() {
        $result = array_merge(parent::defineParams(),
        array(
        'active'=>false
        ));
        return $result;
    }

    /**
     * Method Description
     *
     * @return type
     * @access protected
     */

     protected function main() {
        parent::main();
        $currencyConverter = CurrencyConverter::getInstance();
        if (isset($_POST['current_currency'])) {
        	$currencyConverter->setCurrent($_POST['current_currency']);
        }
        $isCurrentField = new Field('is_current');
        foreach ($currencyConverter->getCurrencies() as $currID) {
            if ($currID == $currencyConverter->getCurrent()) {
                $isCurrentField->addRowData(true);
            }
            else {
            	$isCurrentField->addRowData(false);
            }

        }
        $this->getData()->addField($isCurrentField);
        $this->addTranslation('MSG_SWITCHER_TIP');
		$this->addTranslation('TXT_CURRENCY_RATE');
     }

}