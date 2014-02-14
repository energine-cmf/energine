<?php
class BuilderTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Data
     */
    private $data;

    /**
     * @var DataDescription
     */
    private $dataDescription;

    public function setUp() {
        // Setting up data description
        $this->dataDescription = new DataDescription();
        // Seting up set of fields
        foreach (array('entity_id' => FieldDescription::FIELD_TYPE_INT,
                       'entity_title' => FieldDescription::FIELD_TYPE_STRING,
                       'entity_date' => FieldDescription::FIELD_TYPE_DATE) as $fieldName => $fieldType) {
            $fd = new FieldDescription($fieldName);
            $fd->setType($fieldType);
            $this->dataDescription->addFieldDescription($fd);
        }
        // Setting up mock data
        $arr = array(
            0 => array(
                'entity_id' => 1,
                'entity_title' => 'test1',
                'entity_date' => date('Y-m-d')
            ),
            1 => array(
                'entity_id' => 2,
                'entity_title' => 'test2',
                'entity_date' => date('Y-m-d')
            ),
            2 => array(
                'entity_id' => 3,
                'entity_title' => 'test3',
                'entity_date' => date('Y-m-d')
            )
        );
        $this->data = new Data();
        $this->data->load($arr);
    }

    /**
     * Trying to build data with empty data and description
     * leads to SystemException
     *
     * @expectedException   SystemException
     */
    public function testBuildException() {
        $builder = new Builder();
        $builder->build();
    }

    public function testBuild() {
        $builder = new Builder();
        // Requesting result before build should led to false result
        $this->assertFalse($builder->getResult());
        // Building Data
        $builder->setDataDescription($this->dataDescription);
        $builder->setData($this->data);
        $builder->build();
        $this->assertInstanceOf('DOMElement', $builder->getResult());
        // Taking into account input data, builder must provide recordset with 3 records
        // @see setUp()
        $this->assertSelectCount('recordset record', 3, $builder->getResult()->ownerDocument->saveXML());
    }

    /**
     * Testing AbstractBuilder functionality on date formatting
     */
    public function testEnFormatDate() {
        $builder = new Builder();
        // 1297693504
        // 14/02/2011 16:25:04
        // For Date only
        $this->assertRegExp('/^.*?,\s\d+\s.*?\s\d{4}$/', $builder->enFormatDate(date('Y-m-d H:i:s', 1297693504), '%f', FieldDescription::FIELD_TYPE_DATE));
        // For Date time
        $this->assertRegExp('/^.*?,\s\d+\s.*?\s\d{4},\s\d{2}\:\d{2}$/', $builder->enFormatDate(date('Y-m-d H:i:s', 1297693504), '%f', FieldDescription::FIELD_TYPE_DATETIME));
        // For Date formatted using %E. Should start with TXT_TODAY translation
        $this->assertStringStartsWith(DBWorker::_translate('TXT_TODAY'), $builder->enFormatDate(date('Y-m-d H:i:s', time()), '%E', FieldDescription::FIELD_TYPE_DATETIME));
    }
}