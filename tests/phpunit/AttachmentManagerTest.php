<?php
class AttachmentManagerTest extends PHPUnit_Framework_TestCase {

    public function testCreateAm() {
        $dd = new DataDescription();
        $data = new Data();
        // first lets try to crate am on table without attachments
        $am = new AttachmentManager($dd, $data, 'frm_forms');
        $am->createFieldDescription();
        $this->assertFalse($data->getFieldByName('attachments'));
        // now trying to create am on table WITH attachments
        $dd = new DataDescription();
        $fd = array(
            'tf_id' => FieldDescription::FIELD_TYPE_INT,
            'tf_name' => FieldDescription::FIELD_TYPE_STRING
        );
        foreach($fd as $fName => $fType) {
            $f = new FieldDescription($fName);
            $f->setType($fType);
            $dd->addFieldDescription($f);
        }
        $data = new Data();
        $dbData = E()->getDB()->select('SELECT a.tf_id, at.tf_name FROM apps_feed a LEFT JOIN apps_feed_translation at ON a.tf_id = at.tf_id');
        $data->load($dbData);
        $am = new AttachmentManager($dd, $data, 'apps_feed');
        $am->createFieldDescription();
        $am->createField('tf_id');
        $this->assertNotNull($data->getFieldByName('attachments'));
    }
}