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
        $this->setOrder('curr_order_num', QAL::ASC);
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
        $currIDField = $this->getData()->getFieldByName('curr_id'); 
        $currRate = $this->getData()->getFieldByName('curr_rate');
        $currString = new Field('curr_string');
        $this->getData()->addField($currString);
        foreach($currIDField as $rowID => $currencyRowData){
        	if($currencyRowData == $currencyConverter->getCurrent()){
        		$currIDField->setRowProperty($rowID, 'current', 'current');
        	}
        	$currString->setRowData(
        	   $rowID, 
        	   $currencyConverter->format(
        	       $currencyConverter->convert(
        	           1,
                       $currencyRowData, 
        	           $currencyConverter->getMain()
        	       ), 
        	       $currencyRowData
        	   )
        	);
        }
        $this->addTranslation('MSG_SWITCHER_TIP', 'TXT_CURRENCY_RATE');
     }

}