<?php
class BuilderTest extends PHPUnit_Framework_TestCase {
    /**
     * @var Builder
     */
    private $builder;

    /**
     * @var Data
     */
    private $data;

    /**
     * @var DataDescription
     */
    private $dataDescription;

    public function setUp() {
        $this->builder = new Builder();
        // Setting up data description
        $dd = new DataDescription();
        // Seting up set of fields
        foreach (array('entity_id' => FieldDescription::FIELD_TYPE_INT,
                       'entity_title' => FieldDescription::FIELD_TYPE_STRING,
                       'entity_date' => FieldDescription::FIELD_TYPE_DATE) as $fieldName => $fieldType) {
            $fd = new FieldDescription($fieldName);
            $fd->setType($fieldType);
            $dd->addFieldDescription($fd);
        }
        // Setting up data
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
        $data = new Data();
        $data->load($arr);
    }

    /**
     * Trying to build data with empty data descriptions
     * leads to Exception/
     *
     * @expectedException   SystemException
     */
    public function testBuildException() {
        $this->builder->build();
    }

    /**
     * Testing AbstractBuilder functionality on date formatting
     */
    public function testEnFormatDate() {
        // 1297693504
        // 14/02/2011 16:25:04
        // For Date only
        $this->assertRegExp('/^.*?,\s\d+\s.*?\s\d{4}$/', $this->builder->enFormatDate(date('Y-m-d H:i:s', 1297693504), '%f', FieldDescription::FIELD_TYPE_DATE));
        // For Date time
        $this->assertRegExp('/^.*?,\s\d+\s.*?\s\d{4},\s\d{2}\:\d{2}$/', $this->builder->enFormatDate(date('Y-m-d H:i:s', 1297693504), '%f', FieldDescription::FIELD_TYPE_DATETIME));
        // For Date formatted using %E. Should start with TXT_TODAY translation
        $this->assertStringStartsWith(DBWorker::_translate('TXT_TODAY'), $this->builder->enFormatDate(date('Y-m-d H:i:s', time()), '%E', FieldDescription::FIELD_TYPE_DATETIME));
    }
}