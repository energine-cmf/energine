<?php
class FieldTest extends PHPUnit_Framework_TestCase {

    public function testCreateField() {
        $f = new Field('');
        $this->assertEquals($f->getName(), '');
        $f = new Field('phpunit');
        $this->assertEquals($f->getName(), 'phpunit');
        // When field is created, data for this field should be empty
        $this->assertTrue(0 === $f->getRowCount());
        $this->assertEmpty($f->getData());
        // Also, we can't get any row data
        $this->assertNull($f->getRowData(0));
    }

    public function testSetData() {
        $f = new Field('phpunit');
        // When we have no data, but still using $setForAll param
        // data is set based on num languages in database.
        $f->setData(3, true);
        $this->assertEquals($f->getRowCount(), sizeof(E()->getLanguage()->getLanguages()));
        $f->setData(array(1,2,3));
        $this->assertTrue(3 === $f->getRowCount());
        // Changing data for first row
        $f->setRowData(0, 5);
        $this->assertEquals(array(5,2,3), $f->getData());
        // Setting new Data for all rows
        $f->setData(1, true);
        foreach($f as $val) {
            $this->assertTrue($val === 1);
        }
        // Replacing Field data
        $f->setData(4);
        $this->assertEquals(array(4), $f->getData());
    }

    public function testRemoveRowData() {
        $f = new Field('phpunit');
        $f->setData(array(1,2,3));
        $f->removeRowData(2);
        $this->assertEquals($f->getRowCount(), 2);
        $f->addRowData(10, false);
    }

    public function testRowProperty() {
        $f = new Field('phpunit');
        $f->setData(array(1,2,3,4,5,6));
        $f->setRowProperty(2, 'test', 'test');
        $f->setRowProperty(2, 'more', 'more');
        $f->setRowProperty(2, 'testFalse', false);
        $this->assertEquals(count($f->getRowProperties(2)), 3);
        $this->assertEquals($f->getRowProperty(2, 'test'), 'test');
        // Trying to get unset row property leads to null result
        $this->assertNull($f->getRowProperty(10, 'test'));
    }
}