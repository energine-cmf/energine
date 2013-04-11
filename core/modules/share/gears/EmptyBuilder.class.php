<?php
/**
 * Класс EmptyBuilder.
 *
 * @package energine
 * @subpackage kernel
 * @author dr.Pavka
 * @copyright Energine 2012
 */


/**
 * Билдер который собственно ничего не билдит
 * Нужен для случаев когда нет необходимости в данных (recordset)
 * Часто это используется когда main state служит просто для загрузки джаваскриптового класса
 * который осуществляет загрузку данных аяксом
 *
 * @package energine
 * @subpackage kernel
 * @author dr.Pavka
 */
class EmptyBuilder implements IBuilder {
    /**
     * Метод реализован
     * @return true
     */
    public function build() {
        return true;
    }

    /**
     * Создаем пустой рекордсет требующийся для привязки к JS
     *
     * @return bool
     */
    public function getResult() {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $dom_recordSet = $doc->createElement('recordset');
        return $dom_recordSet;
    }
}
