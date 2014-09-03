<?php
/**
 * Содержит класс ProductFeed
 *
 * @package energine
 * @subpackage shop
 * @author Andrii A
 * @copyright Energine 2013
 */

/**
 * Отвечает за вывод списка товаров.
 *
 * @package energine
 * @subpackage shop
 * @author Andrii A
 */
class ProductFeed extends ExtendedFeed {
    /**
     * Конструктор класса
     *
     * @access public
     */
    public function __construct($name, $module, array $params = null) {
        parent::__construct($name, $module, $params);
        $this->setTableName('shop_product');
    }

    protected function view() {
        $id = $this->getStateParams();
        list($id) = $id;
        if (!$this->recordExists($id)) {
            throw new SystemException('ERR_404', SystemException::ERR_404);
        }
        $this->addFilterCondition(array($this->getTableName() . '.' . $this->getPK() => $id));
        $this->prepare();
        $this->createPropertiesField(
            $this->getProductProperties($id)
        );
    }

    private function createPropertiesField($data) {
        $fd = new FieldDescription('properties');
        $fd->setType(FieldDescription::FIELD_TYPE_CUSTOM);
        $this->getDataDescription()->addFieldDescription($fd);
        $f = new Field('properties');

        // Build data for properties field
        $dataDescription = new DataDescription();
        $fd = new FieldDescription('prop_id');
        $fd->setType(FieldDescription::FIELD_TYPE_INT);
        $dataDescription->addFieldDescription($fd);

        $fd = new FieldDescription('prop_title');
        $fd->setType(FieldDescription::FIELD_TYPE_STRING);
        $dataDescription->addFieldDescription($fd);

        $fd = new FieldDescription('values');
        $fd->setType(FieldDescription::FIELD_TYPE_CUSTOM);
        $dataDescription->addFieldDescription($fd);
        $b = new SimpleBuilder();
        $b->setDataDescription($dataDescription);
        $d = new Data();
        $d->load($data);
        $b->setData($d);
        $b->build();
        $f->setData($b->getResult(), true);
        $this->getData()->addField($f);
    }

    private function getProductProperties($product) {
        $properties = array();
        $propertiesData = $this->dbh->select('SELECT p2p.prop_id, ppt.prop_title, p2p.pval_id, pvt.pval_title
                                            FROM shop_product2property p2p
                                            LEFT JOIN shop_product_properties_translation ppt ON ppt.prop_id = p2p.prop_id
                                            LEFT JOIN shop_product_properties_values_translation pvt ON pvt.pval_id = p2p.pval_id
                                            WHERE product_id = %s
                                            AND ppt.lang_id = %s
                                            AND pvt.lang_id = %2$s', $product, $this->document->getLang());
        if(is_array($propertiesData)) {
            array_walk(
                $propertiesData,
                function($row) use (&$properties) {
                    $properties[$row['prop_id']]['prop_id'] = $row['prop_id'];
                    $properties[$row['prop_id']]['prop_title'] = $row['prop_title'];
                    $properties[$row['prop_id']]['values'][] = array(
                        'pval_id' => $row['pval_id'],
                        'pval_title' => $row['pval_title']
                    );
                }
            );
            foreach($properties as &$property) {
                $property['values'] = $this->buildPropertyValues($property['values']);
            }
        }
        return $properties;
    }

    private function buildPropertyValues($data) {
        static $dataDescription;
        if (!isset($dataDescription)) {
            $dataDescription = new DataDescription();
            $fd = new FieldDescription('pval_id');
            $fd->setType(FieldDescription::FIELD_TYPE_INT);
            $dataDescription->addFieldDescription($fd);

            $fd = new FieldDescription('pval_title');
            $fd->setType(FieldDescription::FIELD_TYPE_STRING);
            $dataDescription->addFieldDescription($fd);
        }
        $b = new SimpleBuilder();
        $b->setDataDescription($dataDescription);
        $d = new Data();
        $d->load($data);
        $b->setData($d);
        $b->build();
        return $b->getResult();
    }
}