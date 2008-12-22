<?php
/**
 * Содержит класс PriceLoader
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 * @copyright ColoCall 2006
 * @version $Id$
 */

//require_once('core/modules/share/components/DataSet.class.php');
//require_once('core/ext/excel/reader.php');

/**
 * Класс для загрузки прайса
 *
 * @package energine
 * @subpackage shop
 * @author dr.Pavka
 */
class PriceLoader extends DataSet {
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
    }
    /**
	 * Переопределен параметр active
	 *
	 * @return int
	 * @access protected
	 */

    protected function defineParams() {
        $result = array_merge(parent::defineParams(),
        array(
        'active'=>true,
        ));
        return $result;
    }
    /**
	 * Добавляем вкладку
	 *
	 * @return void
	 * @access protected
	 */

    protected function main() {
        $this->setDataSetAction('load');
        $this->setType(self::COMPONENT_TYPE_FORM_ADD);
        parent::main();
        $this->addTab($this->buildTab($this->translate('TXT_LOAD_PRICE')));
    }

    /**
	 * Загрузка файла
	 *
	 * @return void
	 * @access protected
	 */

    protected function loadFile() {
        $this->prepare();
        $fieldName = (string)$this->config->getMethodConfig(self::DEFAULT_ACTION_NAME)->fields->field[0]['name'];
        $newFilename = $this->document->getSiteRoot().'/tmp/'.date('YmdHis');
        if (empty($_FILES) || !isset($_FILES[$fieldName]) || !empty($_FILES[$fieldName]['error']) || !is_readable($_FILES[$fieldName]['tmp_name']) || !is_uploaded_file($_FILES[$fieldName]['tmp_name']) || !move_uploaded_file($_FILES[$fieldName]['tmp_name'], $newFilename)) {
            throw new SystemException('ERR_BAD_FILE', SystemException::ERR_CRITICAL);
        }
        chmod($newFilename, 0666);
        $this->document->componentManager->getComponentByName('breadCrumbs')->addCrumb();

        $resultMessage = $this->parseFileAndLoadData($newFilename);

        unlink($newFilename);
        $data = new Data();

        $resultField = new Field('price_loader_result');
        $resultField->setData($resultMessage);
        $data->addField($resultField);
        $this->setData($data);
    }

    /**
     * parse & prepare
     *
     * @return void
     * @access protected
     */

    protected function parseFileAndLoadData($filename) {
        $data = new Spreadsheet_Excel_Reader();
        $data->setRowColOffset(0);
        $data->setUTFEncoder('mb');
        $data->setOutputEncoding('UTF8');
        $this->dbh->beginTransaction();
        $resultMessage = $this->translate('MSG_IMPORT_SUCCESS');
        try {
            //$this->dbh->modify(QAL::UPDATE , 'shop_products', array('product_is_available'=>'0'));
            $this->dbh->modify(QAL::DELETE , 'shop_product_external_properties');
            $data->read($filename);
            $rowCount = $data->sheets[0]['numRows'];

            for ($rowNum = 0; $rowNum < $rowCount; $rowNum++) {
                if (isset($data->sheets[0]['cells'][$rowNum])) {
                    $code = trim($data->sheets[0]['cells'][$rowNum][0]);
                    $price = $data->sheets[0]['cells'][$rowNum][1];
                    $productID = simplifyDBResult($this->dbh->select('shop_products', array('product_id'), array('product_code'=>$code)), 'product_id', true);
                    if ($productID) {
                        $this->dbh->modify(QAL::INSERT , 'shop_product_external_properties', array('product_code'=>$code, 'product_price'=>$price));
                        //$this->dbh->modify(QAL::UPDATE , 'shop_products', array('product_is_available'=>1), array('product_id'=>$productID));
                    }
                }
            }

            $this->dbh->commit();

        }
        catch (Exception $e) {
            $this->dbh->rollback();
            $this->generateError(SystemException::ERR_NOTICE ,$e->getMessage());

            $resultMessage = $this->translate('MSG_IMPORT_FAILED').': '.$e->getMessage();
        }

        return $resultMessage;
    }
}
