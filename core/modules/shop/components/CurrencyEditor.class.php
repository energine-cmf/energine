<?php
/**
 * Содержит класс CurrencyEditor
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 * @copyright ColoCall 2006
 * @version $Id$
 */

//require_once('core/modules/share/components/Grid.class.php');

/**
 * Класс предназначен для редактирования перечня валюты
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 */
class CurrencyEditor extends Grid {
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
        $this->setTableName('shop_currency');
	}

    /**
     * Переопределенный метод
     * Для формы редактирования, если чекбокс валюты по умолчанию отмечен делает его неактивным
     *
     * @return void
     * @access public
     */

    public function build() {
        if ($this->getAction() !== self::DEFAULT_ACTION_NAME ) {
            $this->getDataDescription()->getFieldDescriptionByName('curr_abbr')->addProperty('pattern', '/[A-Z]{3}/');
            $this->getDataDescription()->getFieldDescriptionByName('curr_abbr')->addProperty('message', $this->translate('MSG_BAD_CURR_ABBR'));
        }
        return parent::build();
    }
}