<?php
/**
 * Содержит класс OrderHistory
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 * @copyright ColoCall 2006
 * @version $Id$
 */

//require_once('core/modules/share/components/Grid.class.php');

/**
 * История заказов
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 */
class OrderHistory extends Grid {
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
        $this->setTableName('shop_orders');
        $this->setOrder('order_created', QAL::DESC);
    }

    /**
     * Формат даты создания заказа
     *
     * @return DataDescription
     * @access protected
     */

     protected function createDataDescription() {
         $result = parent::createDataDescription();
         if ($this->getAction() == 'getRawData') {
         	$result->getFieldDescriptionByName('order_created')->setProperty('outputFormat', '%d/%m/%Y %H:%M');
         }
         return $result;
     }
     
     
    /**
     * Для метода вывода информации о заказе доавбляем инфу о данных заказа
     *
     * @return void
     * @access protected
     */

     protected function edit() {
        parent::edit();

        $data = $this->dbh->select($this->getTableName(), array('user_detail', 'order_detail'), $this->getFilter());
        if (is_array($data)) {
            list($data) = $data;
            $userData = unserialize($data['user_detail']);
            $orderData = unserialize($data['order_detail']);
            unset($userData['u_id']);
            foreach ($userData as $fieldName => $value) {
                $field = new FieldDescription($fieldName);
                $field->setType(FieldDescription::FIELD_TYPE_STRING);
                $field->setProperty('customField', true);
                $field->setMode(FieldDescription::FIELD_MODE_READ);
                $this->getDataDescription()->addFieldDescription($field);

                $field = new Field($fieldName);
                $field->setData($value);
                $this->getData()->addField($field);
            }
        }
        $f = new Field('order_details');
        $f->setData($this->buildDetailField($orderData), true);
        $this->getData()->addField($f);
     }
     
     private function buildDetailField($data){
     	$builder = new SimpleBuilder();
        $dd = new DataDescription();
        $dd->loadXML($this->config->getMethodConfig('order_details')->fields);
        $builder->setDataDescription($dd);
        
        $d = new Data();
        $d->load($data);
        $builder->setData($d);
        
        $builder->build();
        return $builder->getResult();
     }
}
