<?php
/**
 * Содержит класс TopOfThePops
 *
 * @package energine
 * @author dr.Pavka
 * @copyright Energine 2015
 */
namespace Energine\apps\components;

use Energine\share\components\DataSet;
use Energine\share\gears\AttachmentManager;
use Energine\share\gears\Data;
use Energine\share\gears\DataDescription;
use Energine\share\gears\Field;
use Energine\share\gears\FieldDescription;
use Energine\share\gears\SimpleBuilder;

/**
 * Top materials
 *
 * @package energine
 * @author dr.Pavka
 */
class TopOfThePops extends DataSet {
    public function __construct($name, $module, array $params = NULL) {
        $params['active'] = false;
        parent::__construct($name, $module, $params);
        $this->setParam('recordsPerPage', NULL);
        $this->setBuilder(new SimpleBuilder());
        $this->setType(self::COMPONENT_TYPE_LIST);
    }

    protected function mainState() {
        $dd = new DataDescription();
        $fd = new FieldDescription('id');
        $fd->setProperty('key', true);
        $fd->setType(FieldDescription::FIELD_TYPE_INT);
        $dd->addFieldDescription($fd);
        $fd = new FieldDescription('name');
        $fd->setType(FieldDescription::FIELD_TYPE_STRING);
        $dd->addFieldDescription($fd);

        $fd = new FieldDescription('data');
        $fd->setType(FieldDescription::FIELD_TYPE_CUSTOM);
        $dd->addFieldDescription($fd);
        $d = new Data();
        $d->load($this->dbh->select('SELECT t.tg_id as id, tg_name as `name`, t.tg_id as data FROM apps_top_groups t LEFT JOIN apps_top_groups_translation tt ON (tt.tg_id = t.tg_id) AND (lang_id=%s) WHERE site_id = %s ORDER by tg_order_num', $this->document->getLang(), E()->SiteManager->getCurrentSite()->id));
        $this->setData($d);
        $this->setDataDescription($dd);
        $this->js = $this->buildJS();
        $this->buildGroups($this->getData()->getFieldByName('data'));
    }

    private function buildGroups(Field $field) {
        $builder = new SimpleBuilder();
        $data = new Data();
        $dd = new DataDescription();
        $dd->load([
            'id' => [
                'type' => FieldDescription::FIELD_TYPE_INT,
                'key' => true
            ],
            'link' => [
                'type' => FieldDescription::FIELD_TYPE_STRING,
            ],
            'title' => [
                'type' => FieldDescription::FIELD_TYPE_STRING
            ],
            'text' => [
                'type' => FieldDescription::FIELD_TYPE_HTML_BLOCK
            ]
        ]);
        $builder->setDataDescription($dd);

        foreach ($field as $key => $id) {
            if ($d = $this->dbh->select('SELECT t.top_id as id, top_name as `title`, top_link as link,top_text_rtf as `text`  FROM apps_tops t LEFT JOIN apps_tops_translation tt ON (tt.top_id=t.top_id) AND (lang_id=%s) WHERE (t.tg_id =%s) AND top_is_active ORDER BY top_order_num', $this->document->getLang(), $id)) {
                $data->load($d);
                $builder->setData($data);
                $am = new AttachmentManager($dd, $data, 'apps_tops');
                $am->createFieldDescription();
                $am->createField('top_id', false, $data->getFieldByName('id')->getData());
                $builder->build();
                $field->setRowData($key, $builder->getResult());
            }
            else {
                $field->setRowData($key, null);
            }
        }
    }


    protected function buildJS() {
        if (!$result = parent::buildJS()) {
            $result = $this->doc->createElement('javascript');
            $behavior = $this->doc->createElement('behavior');
            $result->appendChild($behavior);
            $behavior->setAttribute('name', 'TOTP');
            $behavior->setAttribute('use', 'jquery');
        }
        return $result;

    }
}